<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Magazine;
use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;

class PostVoter extends Voter
{
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const PURGE = 'purge';
    const COMMENT = 'comment';
    const VOTE = 'vote';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Post
            && \in_array(
                $attribute,
                [self::CREATE, self::EDIT, self::DELETE, self::PURGE, self::COMMENT, self::VOTE],
                true
            );
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
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::PURGE:
                return $this->canPurge($subject, $user);
            case self::COMMENT:
                return $this->canComment($subject, $user);
            case self::VOTE:
                return $this->canVote($subject, $user);
        }

        throw new \LogicException();
    }

    private function canEdit(Post $post, User $user): bool
    {
        if ($post->getUser() === $user) {
            return true;
        }

        if ($post->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Post $post, User $user): bool
    {
        if ($post->getUser() === $user) {
            return true;
        }

        if ($post->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canPurge(Post $post, User $user): bool
    {
        if ($post->getUser() === $user) {
            return true;
        }

        if ($post->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canComment(Post $post, User $user): bool
    {
        return !$post->getMagazine()->isBanned($user);
    }

    private function canVote(Post $post, User $user): bool
    {
        if ($post->getUser() === $user) {
            return false;
        }

        if ($post->getMagazine()->isBanned($user)) {
            return false;
        }

        return true;
    }
}
