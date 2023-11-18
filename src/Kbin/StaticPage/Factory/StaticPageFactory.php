<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\StaticPage\Factory;

use App\Kbin\StaticPage\DTO\StaticPageDto;
use App\Repository\PageRepository;

readonly class StaticPageFactory
{
    public function __construct(private PageRepository $pageRepository)
    {
    }

    public function createDtoFromName(string $name): StaticPageDto
    {
        $page = $this->pageRepository->findOneBy(['name' => $name]);

        if ($page) {
            $dto = new StaticPageDto();
            $dto->title = $page->title;
            $dto->body = $page->body;
            $dto->lang = $page->lang;

            return $dto;
        }

        return new StaticPageDto();
    }
}
