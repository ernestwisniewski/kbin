<?php declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Magazine;
use Webmozart\Assert\Assert;

class MagazineVoter extends Voter
{
    const EDIT = 'edit';
    const PURGE = 'purge';
    const MODERATE = 'moderate';

    protected function supports(string $attribute, $subject)
    {
        return $subject instanceof Magazine && \in_array($attribute, [self::EDIT, self::PURGE, self::MODERATE], true);
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
            case self::MODERATE:
                return $this->canModerate($subject, $user);
        }

        return new \LogicException();
    }

    private function canEdit(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user);
    }

    private function canPurge(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user);
    }

    private function canModerate(Magazine $magazine, $user): bool
    {
        return $magazine->userIsModerator($user);
    }

}
