<?php declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\Magazine;
use App\Entity\User;

interface NotificationInterface extends ContentInterface
{
    public function getId(): ?int;

    public function getMagazine(): ?Magazine;

    public function getUser(): ?User;

    public function getSubjectClassName(): string;
}
