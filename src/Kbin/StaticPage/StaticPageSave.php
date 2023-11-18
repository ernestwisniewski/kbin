<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\StaticPage;

use App\Entity\Page;
use App\Kbin\StaticPage\DTO\StaticPageDto;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class StaticPageSave
{
    public function __construct(
        private PageRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(string $name, StaticPageDto $dto): Page
    {
        $page = $this->repository->findOneBy(['name' => $name]);

        if (!$page) {
            $page = new Page();
        }

        $page->name = $name;
        $page->title = $dto->title;
        $page->body = $dto->body;
        $page->lang = $dto->lang;

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
