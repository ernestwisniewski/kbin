<?php declare(strict_types = 1);

namespace App\DataFixtures\Factory;

use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;

class EntityFactory
{
    public static function createUser(?string $email = null, ?string $username = null): User
    {
        return new User(
            $email ?? 'regularUser@example.com',
            $username ?? 'regularUser',
            'secret'
        );
    }

    public static function createMagazine(?string $name = null, ?string $title = null, ?User $user = null): Magazine
    {
        return new Magazine(
            $name ?? 'polityka',
            $title ?? 'Magazyn polityczny',
            $user ?? self::createUser()
        );
    }

    public static function createEntry(
        ?string $title = null,
        ?string $url = null,
        ?string $body = null,
        ?Magazine $magazine = null,
        ?User $user = null
    ): Entry {
        return new Entry(
            $title ?? 'Przykładowy wpis',
            $url ?? 'https://example.com',
            $body,
            $magazine ?? self::createMagazine(),
            $user ?? self::createUser()
        );
    }

    public static function createEntryComment(?string $body = null, ?Entry $entry = null, ?User $user = null): EntryComment
    {
        return new EntryComment(
            $body ?? 'przykładowy komentarz',
            $entry ?? self::createEntry(),
            $user ?? $entry->getUser()
        );
    }
}
