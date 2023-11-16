<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\DTO\ModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;

class MagazineModeratorsApiTest extends WebTestCase
{
    public function testApiCannotAddModeratorsToMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $user = $this->getUserByUsername('notamod');
        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/mod/{$user->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRemoveModeratorsFromMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $user = $this->getUserByUsername('yesamod');
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $user;
        $magazineModeratorAdd($dto);

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/mod/{$user->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotAddModeratorsToMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $user = $this->getUserByUsername('notamod');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST',
            "/api/moderate/magazine/{$magazine->getId()}/mod/{$user->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRemoveModeratorsFromMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $user = $this->getUserByUsername('yesamod');
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $user;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/moderate/magazine/{$magazine->getId()}/mod/{$user->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotAddModeratorsMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $user = $this->getUserByUsername('notamod');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write moderate:magazine_admin:moderators'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST',
            "/api/moderate/magazine/{$magazine->getId()}/mod/{$user->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotRemoveModeratorsMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $user = $this->getUserByUsername('yesamod');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineModeratorAdd($dto);
        $dto = new ModeratorDto($magazine);
        $dto->user = $user;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write moderate:magazine_admin:moderators'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/moderate/magazine/{$magazine->getId()}/mod/{$user->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiOwnerCanAddModeratorsMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $moderator = $this->getUserByUsername('willbeamod');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write moderate:magazine_admin:moderators'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST',
            "/api/moderate/magazine/{$magazine->getId()}/mod/{$moderator->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(2, $jsonData['moderators']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][1]);
        self::assertSame($moderator->getId(), $jsonData['moderators'][1]['userId']);
    }

    public function testApiOwnerCanRemoveModeratorsMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $moderator = $this->getUserByUsername('yesamod');
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write moderate:magazine_admin:moderators'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(2, $jsonData['moderators']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][1]);
        self::assertSame($moderator->getId(), $jsonData['moderators'][1]['userId']);

        $client->request(
            'DELETE',
            "/api/moderate/magazine/{$magazine->getId()}/mod/{$moderator->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(1, $jsonData['moderators']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][0]);
        self::assertSame($user->getId(), $jsonData['moderators'][0]['userId']);
    }
}
