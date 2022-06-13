<?php declare(strict_types=1);

namespace App\Tests\Controller\Domain;

trait DomainFixturesTrait
{
    private function createEntryFixtures(): void
    {
        $this->createEntry(
            'karabin1',
            $this->getMagazineByName('karabin'),
            $this->getUserByUsername('JohnDoe'),
            'https://karab.in/statystyki'
        );
        $this->createEntry(
            'karabin2',
            $this->getMagazineByName('karabin'),
            $this->getUserByUsername('JohnDoe'),
            'https://karab.in/statystyki'
        );
        $this->createEntry(
            'google',
            $this->getMagazineByName('karabin'),
            $this->getUserByUsername('JohnDoe'),
            'https://google.pl'
        );
    }

    private function createCommentFixtures(): void
    {
        $this->createEntryComment('comment1', $this->getEntryByTitle('karabin1'));
        $this->createEntryComment('comment2', $this->getEntryByTitle('karabin2'));
        $this->createEntryComment('comment3', $this->getEntryByTitle('google'));
    }
}
