<?php declare(strict_types = 1);

namespace App\Factory;

use App\DTO\BadgeDto;
use App\Entity\Badge;

class BadgeFactory
{
    public function createDto(Badge $badge): BadgeDto
    {
        return (new BadgeDto())->create(
            $badge->magazine,
            $badge->name,
            $badge->getId(),
        );
    }
}
