<?php declare(strict_types = 1);

namespace App\Service\Contracts;

use App\Entity\Contracts\ContentInterface;

interface ContentNotificationManagerInterface extends ManagerInterface
{
    public function sendCreated(ContentInterface $subject): void;

    public function sendEdited(ContentInterface $subject): void;

    public function sendDeleted(ContentInterface $subject): void;
}
