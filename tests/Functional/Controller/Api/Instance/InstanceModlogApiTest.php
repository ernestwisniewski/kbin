<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Instance;

use App\Tests\WebTestCase;

class InstanceModlogApiTest extends WebTestCase
{
    public function testApiCanRetrieveModlogAnonymous(): void
    {
        $client = self::createClient();
        $this->createModlogMessages();

        $client->request('GET', '/api/modlog');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertCount(5, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(5, $jsonData['pagination']['count']);

        $magazine = $this->getMagazineByName('acme');
        $moderator = $magazine->getOwner();

        $this->validateModlog($jsonData, $magazine, $moderator);
    }

    public function testApiCanRetrieveModlog(): void
    {
        $client = self::createClient();
        $this->createModlogMessages();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/modlog', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertCount(5, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(5, $jsonData['pagination']['count']);

        $magazine = $this->getMagazineByName('acme');
        $moderator = $magazine->getOwner();

        $this->validateModlog($jsonData, $magazine, $moderator);
    }
}
