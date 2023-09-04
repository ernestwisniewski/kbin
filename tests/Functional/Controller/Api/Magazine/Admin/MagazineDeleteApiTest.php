<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class MagazineDeleteApiTest extends WebTestCase
{
    public function testApiCannotDeleteMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeleteMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiUserCannotDeleteUnownedMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotDeleteUnownedMagazine(): void
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

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeleteMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(204);
    }
}
