<?php declare(strict_types = 1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Entry;
use App\Entity\User;

class EntryVoter extends Voter
{
    const EDIT = 'edit';
    const PURGE = 'purge';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Entry && \in_array($attribute, [self::EDIT, self::PURGE], true);
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
        }

        throw new \LogicException();
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
}
