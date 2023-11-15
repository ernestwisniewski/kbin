<?php

namespace App\Service;

use App\DTO\PageDto;
use App\Entity\Page;
use App\Factory\PageFactory;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class PageManager
{
    public function __construct(
        private PageRepository $repository,
        private EntityManagerInterface $entityManager,
        private PageFactory $factory
    ) {
    }

    public function save(string $name, PageDto $dto): Page
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

    public function getDto(string $name): PageDto
    {
        $page = $this->repository->findOneBy(['name' => $name]);

        if ($page) {
            return $this->createDto($page);
        }

        return new PageDto();
    }

    public function createDto(Page $page): PageDto
    {
        return $this->factory->createDto($page);
    }
}