<?php declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\EntryComment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;

class EntryCommentVoter extends Voter
{
    const EDIT = 'edit';
    const PURGE = 'purge';

    protected function supports(string $attribute, $subject)
    {
        return $subject instanceof EntryComment && \in_array($attribute, [self::EDIT, self::PURGE], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
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
        }

        return new \LogicException();
    }

    private function canEdit(EntryComment $comment, User $user): bool
    {
        if ($comment->getUser() === $user) {
            return true;
        }

        if ($comment->getEntry()->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canPurge(EntryComment $comment, User $user): bool
    {
        if ($comment->getUser() === $user) {
            return true;
        }

        if ($comment->getEntry()->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }
}
