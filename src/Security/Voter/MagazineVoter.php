<?php declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Magazine;
use App\Entity\User;

class MagazineVoter extends Voter
{
    const CREATE_CONTENT = 'create_content';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const PURGE = 'purge';
    const MODERATE = 'moderate';
    const SUBSCRIBE = 'subscribe';
    const BLOCK = 'block';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Magazine
            && \in_array(
                $attribute,
                [self::CREATE_CONTENT, self::EDIT, self::DELETE, self::PURGE, self::MODERATE, self::SUBSCRIBE, self::BLOCK],
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
            case self::CREATE_CONTENT:
                return $this->canCreateContent($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::PURGE:
                return $this->canPurge($subject, $user);
            case self::MODERATE:
                return $this->canModerate($subject, $user);
            case self::SUBSCRIBE:
                return $this->canSubscribe($subject, $user);
            case self::BLOCK:
                return $this->canBlock($subject, $user);
        }

        throw new \LogicException();
    }

    private function canCreateContent(Magazine $magazine, User $user): bool
    {
        return !$magazine->isBanned($user);
    }

    private function canEdit(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsOwner($user);
    }

    private function canDelete(Magazine $magazine, User $user): bool
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

    public function canSubscribe(Magazine $magazine, User $user): bool
    {
        return true;
    }

    public function canBlock(Magazine $magazine, User $user): bool
    {
        if ($magazine->userIsOwner($user)) {
            return false;
        }

        return true;
    }
}
