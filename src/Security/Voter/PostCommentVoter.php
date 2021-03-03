<?php declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\PostComment;
use App\Entity\User;

class PostCommentVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';
    const PURGE = 'purge';
    const VOTE = 'vote';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof PostComment && \in_array($attribute, [self::EDIT, self::DELETE, self::PURGE, self::VOTE], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::PURGE:
                return $this->canPurge($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::VOTE:
                return $this->canVote($subject, $user);
        }

        throw new \LogicException();
    }

    private function canEdit(PostComment $comment, User $user): bool
    {
        if ($comment->getUser() === $user) {
            return true;
        }

        if ($comment->getPost()->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canPurge(PostComment $comment, User $user): bool
    {
        if ($comment->getUser() === $user) {
            return true;
        }

        if ($comment->getPost()->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canDelete(PostComment $comment, User $user): bool
    {
        if ($comment->getUser() === $user) {
            return true;
        }

        if ($comment->getPost()->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canVote(PostComment $comment, User $user): bool
    {
        if ($comment->getUser() === $user) {
            return false;
        }

        if ($comment->getPost()->getMagazine()->isBanned($user)) {
            return false;
        }

        return true;
    }
}
