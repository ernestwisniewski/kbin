<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\DTO\MagazineLogResponseDto;

trait ValidationTrait
{
    public function validateModlog(array $jsonData, Magazine $magazine, User $moderator): void
    {
        foreach ($jsonData['items'] as $item) {
            self::assertArrayKeysMatch(WebTestCase::LOG_ENTRY_KEYS, $item);
            self::assertIsArray($item['magazine']);
            self::assertArrayKeysMatch(WebTestCase::MAGAZINE_SMALL_RESPONSE_KEYS, $item['magazine']);
            self::assertSame($magazine->getId(), $item['magazine']['magazineId']);
            self::assertArrayKeysMatch(WebTestCase::USER_SMALL_RESPONSE_KEYS, $item['moderator']);
            self::assertSame($moderator->getId(), $item['moderator']['userId']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $item['createdAt'], 'createdAt date format invalid');
            self::assertContains($item['type'], MagazineLogResponseDto::LOG_TYPES, 'Log type invalid!');
            switch ($item['type']) {
                case 'log_entry_deleted':
                case 'log_entry_restored':
                    self::assertArrayKeysMatch(WebTestCase::ENTRY_RESPONSE_KEYS, $item['subject']);
                    break;
                case 'log_entry_comment_deleted':
                case 'log_entry_comment_restored':
                    self::assertArrayKeysMatch(WebTestCase::ENTRY_COMMENT_RESPONSE_KEYS, $item['subject']);
                    break;
                case 'log_post_deleted':
                case 'log_post_restored':
                    self::assertArrayKeysMatch(WebTestCase::POST_RESPONSE_KEYS, $item['subject']);
                    break;
                case 'log_post_comment_deleted':
                case 'log_post_comment_restored':
                    self::assertArrayKeysMatch(WebTestCase::POST_COMMENT_RESPONSE_KEYS, $item['subject']);
                    break;
                case 'log_ban':
                case 'log_unban':
                    self::assertArrayKeysMatch(WebTestCase::BAN_RESPONSE_KEYS, $item['subject']);
                    break;
                default:
                    self::assertTrue(false, 'This should not be reached');
                    break;
            }
        }
    }
}
