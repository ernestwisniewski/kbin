<?php declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\Domain;

interface DomainInterface
{
    public function getUrl();

    public function setDomain(Domain $domain): self;
}
