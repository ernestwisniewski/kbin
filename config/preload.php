<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

if (file_exists(\dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require \dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}
