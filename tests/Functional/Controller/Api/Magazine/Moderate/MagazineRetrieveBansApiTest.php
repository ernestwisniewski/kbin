<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Moderate;

use App\DTO\MagazineBanDto;
use App\Service\MagazineManager;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;

class MagazineRetrieveBansApiTest extends WebTestCase
{
    public const BAN_RESPONSE_KEYS = ['banId', 'reason', 'expired', 'expiredAt', 'bannedUser', 'bannedBy', 'magazine'];

    public function testApiCannotRetrieveMagazineBansAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('GET', "/api/moderate/magazine/{$magazine->getId()}/bans");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveMagazineBansWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/moderate/magazine/{$magazine->getId()}/bans", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveMagazineBansIfNotMod(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:ban:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $magazine = $this->getMagazineByName('test', $this->getUserByUsername('JaneDoe'));
        $client->request('GET', "/api/moderate/magazine/{$magazine->getId()}/bans", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveMagazineBans(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');

        $bannedUser = $this->getUserByUsername('hapless_fool');
        $magazineManager = $this->getService(MagazineManager::class);
        $ban = MagazineBanDto::create('test ban :)');
        $magazineManager->ban($magazine, $bannedUser, $user, $ban);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:ban:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/moderate/magazine/{$magazine->getId()}/bans", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::BAN_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals($ban->reason, $jsonData['items'][0]['reason']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['bannedUser']);
        self::assertSame($bannedUser->getId(), $jsonData['items'][0]['bannedUser']['userId']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['bannedBy']);
        self::assertSame($user->getId(), $jsonData['items'][0]['bannedBy']['userId']);
        self::assertNull($jsonData['items'][0]['expiredAt']);
        self::assertFalse($jsonData['items'][0]['expired']);
    }
}
