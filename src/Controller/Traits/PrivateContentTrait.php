<?php

declare(strict_types=1);

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
            if (null === $this->getUser()) {
                throw $this->createAccessDeniedException();
            }

            if (false === $this->getUser()->isFollowing($entry->user)) {
                throw $this->createAccessDeniedException();
            }
        }
    }
}
