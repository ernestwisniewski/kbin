<?php

namespace App\Service;

use App\Factory\EntryFactory;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\User;

class EntryManager
{
    /**
     * @var EntryFactory
     */
    private $entryFactory;

    public function __construct(EntryFactory $entryFactory) {

        $this->entryFactory = $entryFactory;
    }

    public function createEntry(EntryDto $entryDto, User $user): Entry
    {
        return $this->entryFactory->createFromDto($entryDto, $user);
    }
}
