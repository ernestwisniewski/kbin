<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Magazine;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function in_array;

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
            && in_array(
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

        return match ($attribute) {
            self::CREATE_CONTENT => $this->canCreateContent($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::PURGE => $this->canPurge($subject, $user),
            self::MODERATE => $this->canModerate($subject, $user),
            self::SUBSCRIBE => $this->canSubscribe($subject, $user),
            self::BLOCK => $this->canBlock($subject, $user),
            default => throw new LogicException(),
        };
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
        return $user->isAdmin();
    }

    private function canModerate(Magazine $magazine, User $user): bool
    {
        return $magazine->userIsModerator($user) || $user->isAdmin();
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
