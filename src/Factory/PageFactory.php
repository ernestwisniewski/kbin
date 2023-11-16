<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\PageDto;
use App\Entity\Page;

class PageFactory
{
    public function createDto(Page $page): PageDto
    {
        $dto = new PageDto();
        $dto->title = $page->title;
        $dto->body = $page->body;
        $dto->lang = $page->lang;

        return $dto;
    }
}
