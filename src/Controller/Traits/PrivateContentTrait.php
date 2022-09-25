<?php

namespace App\Controller\Traits;

use App\Entity\Contracts\ContentInterface;

/**
 * @method getUserOrThrow()
 * @method createAccessDeniedException()
 */
trait PrivateContentTrait
{
    private function handlePrivateContent(ContentInterface $entry): void
    {
        if (true === $entry->isPrivate()) {
            $user = $this->getUserOrThrow();

            if (false === $user->isFollowing($entry->user)) {
                throw $this->createAccessDeniedException();
            }
        }
    }
}