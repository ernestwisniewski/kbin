<?php declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\MessageThread;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function in_array;

class MessageThreadVoter extends Voter
{
    const SHOW = 'show';
    const REPLY = 'reply';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof MessageThread
            && in_array(
                $attribute,
                [self::SHOW, self::REPLY],
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
            self::SHOW => $this->canShow($subject, $user),
            self::REPLY => $this->canReply($subject, $user),
            default => throw new LogicException(),
        };
    }

    private function canShow(MessageThread $thread, User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$thread->userIsParticipant($user)) {
            return false;
        }

        return true;
    }

    private function canReply(MessageThread $thread, User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$thread->userIsParticipant($user)) {
            return false;
        }

        return true;
    }

}
