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

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::PURGE => $this->canPurge($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VOTE => $this->canVote($subject, $user),
            default => throw new \LogicException(),
        };
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
        return $user->isAdmin();
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
