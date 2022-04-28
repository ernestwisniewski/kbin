<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

trait DomainFixturesTrait
{
    private function createFixtures(): void
    {
        $this->createEntry(
            'karabin1',
            $this->getMagazineByName('karabin'),
            $this->getUserByUsername('regularUser'),
            'https://karab.in/statystyki'
        );
        $this->createEntry(
            'karabin2',
            $this->getMagazineByName('karabin'),
            $this->getUserByUsername('regularUser'),
            'https://karab.in/statystyki'
        );
        $this->createEntry(
            'google',
            $this->getMagazineByName('karabin'),
            $this->getUserByUsername('regularUser'),
            'https://google.pl'
        );
    }
}
