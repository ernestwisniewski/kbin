<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Tests\WebTestCase;

class MagazinePurgeApiTest extends WebTestCase
{
    public function testApiCannotPurgeMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('DELETE', "/api/admin/magazine/{$magazine->getId()}/purge");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotPurgeMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/admin/magazine/{$magazine->getId()}/purge",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonAdminUserCannotPurgeMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write admin:magazine:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/admin/magazine/{$magazine->getId()}/purge",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotPurgeMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $dto = new MagazineModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineModeratorAdd($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write admin:magazine:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/admin/magazine/{$magazine->getId()}/purge",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiOwnerCannotPurgeMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write admin:magazine:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/admin/magazine/{$magazine->getId()}/purge",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiAdminCanPurgeMagazine(): void
    {
        $client = self::createClient();
        $admin = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $owner = $this->getUserByUsername('JaneDoe');
        $client->loginUser($admin);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write admin:magazine:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'DELETE',
            "/api/admin/magazine/{$magazine->getId()}/purge",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(204);
    }
}
