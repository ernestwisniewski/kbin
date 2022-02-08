<?php declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\User;

interface FavouriteInterface extends ContentInterface
{
    public function getId(): ?int;

    public function getUser(): ?User;

    public function updateCounts(): self;

    public function isFavored(User $user): bool;
}
