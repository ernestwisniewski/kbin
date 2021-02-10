<?php declare(strict_types = 1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineVoter extends Voter
{
    const EDIT = 'edit';
    const PURGE = 'purge';
    const MODERATE = 'moderate';
    const SUBSCRIBE = 'subscribe';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Magazine && \in_array($attribute, [self::EDIT, self::PURGE, self::MODERATE, self::SUBSCRIBE], true);
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
            case self::MODERATE:
                return $this->canModerate($subject, $user);
            case self::SUBSCRIBE:
                return $this->canSubscribe($subject, $user);
        }

        throw new \LogicException();
    }

    private function canEdit(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user);
    }

    private function canPurge(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user);
    }

    private function canModerate(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsModerator($user);
    }

    public function canSubscribe(Magazine $magazine, User $user):bool {
        if($magazine->userIsOwner($user)) {
            return false;
        }

        return true;
    }
}
