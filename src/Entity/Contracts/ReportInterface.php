<?php declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\Magazine;
use App\Entity\User;

interface ReportInterface extends ContentInterface
{
    public function getId(): ?int;

    public function getMagazine(): ?Magazine;

    public function getUser(): ?User;

    public function getReportClassName(): string;
}
