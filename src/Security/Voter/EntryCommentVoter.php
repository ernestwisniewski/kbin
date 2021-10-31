<?php declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\EntryComment;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function in_array;

class EntryCommentVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';
    const PURGE = 'purge';
    const VOTE = 'vote';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof EntryComment && in_array($attribute, [self::EDIT, self::DELETE, self::PURGE, self::VOTE], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::PURGE => $this->canPurge($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VOTE => $this->canVote($subject, $user),
            default => throw new LogicException(),
        };
    }

    private function canEdit(EntryComment $comment, User $user): bool
    {
        if ($comment->user === $user) {
            return true;
        }

        return false;
    }

    private function canPurge(EntryComment $comment, User $user): bool
    {
        return $user->isAdmin();
    }

    private function canDelete(EntryComment $comment, User $user): bool
    {
        if ($comment->user === $user) {
            return true;
        }

        if ($comment->entry->magazine->userIsModerator($user)) {
            return true;
        }

        return false;
    }

    private function canVote(EntryComment $comment, User $user): bool
    {
        if ($comment->user === $user) {
            return false;
        }

        if ($comment->entry->magazine->isBanned($user)) {
            return false;
        }

        return true;
    }
}
