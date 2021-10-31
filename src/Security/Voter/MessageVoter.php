<?php declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function in_array;

class MessageVoter extends Voter
{
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Message
            && in_array(
                $attribute,
                [self::DELETE],
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
            self::DELETE => $this->canDelete($subject, $user),
            default => throw new LogicException(),
        };
    }

    private function canDelete(Message $message, User $user): bool
    {
        return false;
    }

}
