<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Magazine;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Entry;
use App\Entity\User;

class EntryVoter extends Voter
{
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const PURGE = 'purge';
    const COMMENT = 'comment';
    const VOTE = 'vote';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Entry
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
            case self::CREATE:
                return $this->canCreate($subject, $user);
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

    private function canCreate(Magazine $magazine, User $user): bool
    {
        return !$magazine->isBanned($user);
    }

    private function canEdit(Entry $entry, User $user): bool
    {
        if ($entry->getUser() === $user) {
            return true;
        }

        if ($entry->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Entry $entry, User $user): bool
    {
        if ($entry->getUser() === $user) {
            return true;
        }

        if ($entry->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canPurge(Entry $entry, User $user): bool
    {
        if ($entry->getUser() === $user) {
            return true;
        }

        if ($entry->getMagazine()->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canComment(Entry $entry, User $user): bool
    {
        return !$entry->getMagazine()->isBanned($user);
    }

    private function canVote(Entry $entry, User $user): bool
    {
        if ($entry->getUser() === $user) {
            return false;
        }

        if ($entry->getMagazine()->isBanned($user)) {
            return false;
        }

        return true;
    }
}
