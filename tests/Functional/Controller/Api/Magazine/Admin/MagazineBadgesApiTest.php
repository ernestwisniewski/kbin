<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\DTO\BadgeDto;
use App\DTO\ModeratorDto;
use App\Service\BadgeManager;
use App\Service\MagazineManager;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;

class MagazineBadgesApiTest extends WebTestCase
{
    public const BADGE_RESPONSE_KEYS = ['magazineId', 'name', 'badgeId'];

    public function testApiCannotAddBadgesToMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->jsonRequest('POST', "/api/moderate/magazine/{$magazine->getId()}/badge", parameters: ['name' => 'test']);

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRemoveBadgesFromMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $badgeManager = $this->getService(BadgeManager::class);
        $badge = $badgeManager->create(BadgeDto::create($magazine, 'test'));

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/badge/{$badge->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotAddBadgesToMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/moderate/magazine/{$magazine->getId()}/badge", parameters: ['name' => 'test'], server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRemoveBadgesFromMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $badgeManager = $this->getService(BadgeManager::class);
        $badge = $badgeManager->create(BadgeDto::create($magazine, 'test'));

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/badge/{$badge->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotAddBadgesMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineManager = $this->getService(MagazineManager::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineManager->addModerator($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:badges');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/moderate/magazine/{$magazine->getId()}/badge", parameters: ['name' => 'test'], server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotRemoveBadgesMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineManager = $this->getService(MagazineManager::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineManager->addModerator($dto);

        $badgeManager = $this->getService(BadgeManager::class);
        $badge = $badgeManager->create(BadgeDto::create($magazine, 'test'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:badges');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/badge/{$badge->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiOwnerCanAddBadgesMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:badges');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/moderate/magazine/{$magazine->getId()}/badge", parameters: ['name' => 'test'], server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['badges']);
        self::assertCount(1, $jsonData['badges']);
        self::assertArrayKeysMatch(self::BADGE_RESPONSE_KEYS, $jsonData['badges'][0]);
        self::assertEquals('test', $jsonData['badges'][0]['name']);
    }

    public function testApiOwnerCanRemoveBadgesMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $badgeManager = $this->getService(BadgeManager::class);
        $badge = $badgeManager->create(BadgeDto::create($magazine, 'test'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:badges');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/badge/{$badge->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['badges']);
        self::assertCount(0, $jsonData['badges']);
    }
}
