<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service\Contracts;

use App\Entity\Contracts\ContentInterface;

interface ContentNotificationManagerInterface extends ManagerInterface
{
    public function sendCreated(ContentInterface $subject): void;

    public function sendEdited(ContentInterface $subject): void;

    public function sendDeleted(ContentInterface $subject): void;
}
