<?php declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\User;

interface ReportInterface extends ContentInterface
{
    public function getId(): ?int;

    public function getUser(): ?User;
}
