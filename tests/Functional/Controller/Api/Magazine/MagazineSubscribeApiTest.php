<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine;

use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class MagazineSubscribeApiTest extends WebTestCase
{
    public function testApiCannotSubscribeToMagazineAnonymously(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');

        $client->request('PUT', '/api/magazine/'.(string) $magazine->getId().'/subscribe');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotSubscribeToMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/magazine/'.(string) $magazine->getId().'/subscribe', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSubscribeToMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:subscribe magazine:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/magazine/'.(string) $magazine->getId().'/subscribe', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertTrue($jsonData['isUserSubscribed']);
        self::assertFalse($jsonData['isBlockedByUser']);

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId(), server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertTrue($jsonData['isUserSubscribed']);
        self::assertFalse($jsonData['isBlockedByUser']);
    }

    public function testApiCannotUnsubscribeFromMagazineAnonymously(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');

        $client->request('PUT', '/api/magazine/'.(string) $magazine->getId().'/unsubscribe');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUnsubscribeFromMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/magazine/'.(string) $magazine->getId().'/unsubscribe', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUnsubscribeFromMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $manager = $this->getService(MagazineManager::class);
        $manager->subscribe($magazine, $user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:subscribe');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/magazine/'.(string) $magazine->getId().'/unsubscribe', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertFalse($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);

        $client->request('GET', '/api/magazine/'.(string) $magazine->getId(), server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertFalse($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }
}
