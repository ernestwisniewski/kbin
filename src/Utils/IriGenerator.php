<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Contracts\ApiResourceInterface;
use Symfony\Component\String\Inflector\EnglishInflector;

class IriGenerator
{
    public static function getIriFromResource(ApiResourceInterface $apiResource): string
    {
        $inflector = new EnglishInflector();

        $classNameParts = explode('\\', \get_class($apiResource));

        $shortClassName = end($classNameParts);

        $pluralName = strtolower($inflector->pluralize($shortClassName)[0]);

        return strtolower("/api/{$pluralName}/{$apiResource->getId()}");
    }
}
