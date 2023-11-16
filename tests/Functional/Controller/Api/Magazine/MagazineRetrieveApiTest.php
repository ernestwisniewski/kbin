<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine;

use App\Kbin\Magazine\MagazineBlock;
use App\Kbin\Magazine\MagazineSubscribe;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class MagazineRetrieveApiTest extends WebTestCase
{
    public const MODERATOR_RESPONSE_KEYS = [
        'magazineId',
        'userId',
        'username',
        'avatar',
        'apId',
    ];

    public const MAGAZINE_COUNT = 20;

    public function testApiCanRetrieveMagazineByIdAnonymously(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');

        $client->request('GET', "/api/magazine/{$magazine->getId()}");

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertSame($magazine->getId(), $jsonData['magazineId']);
        self::assertIsArray($jsonData['owner']);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['owner']);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['owner']['userId']);
        self::assertNull($jsonData['icon']);
        self::assertNull($jsonData['tags']);
        self::assertEquals('test', $jsonData['name']);
        self::assertIsArray($jsonData['badges']);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(1, $jsonData['moderators']);
        self::assertIsArray($jsonData['moderators'][0]);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][0]);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['moderators'][0]['userId']);

        self::assertFalse($jsonData['isAdult']);
        // Anonymous access, so these values should be null
        self::assertNull($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveMagazineById(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertSame($magazine->getId(), $jsonData['magazineId']);
        self::assertIsArray($jsonData['owner']);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['owner']);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['owner']['userId']);
        self::assertNull($jsonData['icon']);
        self::assertNull($jsonData['tags']);
        self::assertEquals('test', $jsonData['name']);
        self::assertIsArray($jsonData['badges']);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(1, $jsonData['moderators']);
        self::assertIsArray($jsonData['moderators'][0]);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][0]);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['moderators'][0]['userId']);

        self::assertFalse($jsonData['isAdult']);
        // Scopes for reading subscriptions and blocklists not granted, so these values should be null
        self::assertNull($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveMagazineByNameAnonymously(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');

        $client->request('GET', '/api/magazine/name/test');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertSame($magazine->getId(), $jsonData['magazineId']);
        self::assertIsArray($jsonData['owner']);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['owner']);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['owner']['userId']);
        self::assertNull($jsonData['icon']);
        self::assertNull($jsonData['tags']);
        self::assertEquals('test', $jsonData['name']);
        self::assertIsArray($jsonData['badges']);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(1, $jsonData['moderators']);
        self::assertIsArray($jsonData['moderators'][0]);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][0]);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['moderators'][0]['userId']);

        self::assertFalse($jsonData['isAdult']);
        // Anonymous access, so these values should be null
        self::assertNull($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveMagazineByName(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazine/name/test', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertSame($magazine->getId(), $jsonData['magazineId']);
        self::assertIsArray($jsonData['owner']);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['owner']);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['owner']['userId']);
        self::assertNull($jsonData['icon']);
        self::assertNull($jsonData['tags']);
        self::assertEquals('test', $jsonData['name']);
        self::assertIsArray($jsonData['badges']);
        self::assertIsArray($jsonData['moderators']);
        self::assertCount(1, $jsonData['moderators']);
        self::assertIsArray($jsonData['moderators'][0]);
        self::assertArrayKeysMatch(self::MODERATOR_RESPONSE_KEYS, $jsonData['moderators'][0]);
        self::assertSame($magazine->getOwner()->getId(), $jsonData['moderators'][0]['userId']);

        self::assertFalse($jsonData['isAdult']);
        // Scopes for reading subscriptions and blocklists not granted, so these values should be null
        self::assertNull($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiMagazineSubscribeAndBlockFlags(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write magazine:subscribe magazine:block'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertFalse($jsonData['isUserSubscribed']);
        self::assertFalse($jsonData['isBlockedByUser']);
    }

    // The 2 next tests exist because changing the subscription status via MagazineManager after calling the API
    //      was causing strange doctrine exceptions. If doctrine did not throw exceptions when modifications
    //      were made, these tests could be rolled into testApiMagazineSubscribeAndBlockFlags above
    public function testApiMagazineSubscribeFlagIsTrueWhenSubscribed(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $magazineSubscribe = $this->getService(MagazineSubscribe::class);
        $magazineSubscribe($magazine, $user);

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write magazine:subscribe magazine:block'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertTrue($jsonData['isUserSubscribed']);
        self::assertFalse($jsonData['isBlockedByUser']);
    }

    public function testApiMagazineBlockFlagIsTrueWhenBlocked(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testuser');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $magazineBlock = $this->getService(MagazineBlock::class);
        $magazineBlock($magazine, $user);
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse(
            $client,
            scopes: 'read write magazine:subscribe magazine:block'
        );
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData);

        // Scopes for reading subscriptions and blocklists granted, so these values should be filled
        self::assertFalse($jsonData['isUserSubscribed']);
        self::assertTrue($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveMagazineCollectionAnonymous(): void
    {
        $client = self::createClient();

        $magazine = $this->getMagazineByName('test');

        $client->request('GET', '/api/magazines');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazineId']);
    }

    public function testApiCanRetrieveMagazineCollection(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazineId']);
        // Scopes not granted
        self::assertNull($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCanRetrieveMagazineCollectionMultiplePages(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazines = [];
        for ($i = 0; $i < self::MAGAZINE_COUNT; ++$i) {
            $magazines[] = $this->getMagazineByNameNoRSAKey("test{$i}");
        }
        $perPage = max((int) ceil(self::MAGAZINE_COUNT / 2), 1);

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazines?perPage={$perPage}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(self::MAGAZINE_COUNT, $jsonData['pagination']['count']);
        self::assertSame($perPage, $jsonData['pagination']['perPage']);
        self::assertSame(1, $jsonData['pagination']['currentPage']);
        self::assertSame(2, $jsonData['pagination']['maxPage']);
        self::assertIsArray($jsonData['items']);
        self::assertCount($perPage, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertAllValuesFoundByName($magazines, $jsonData['items']);
    }

    public function testApiCannotRetrieveMagazineSubscriptionsAnonymous(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/magazines/subscribed');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveMagazineSubscriptionsWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveMagazineSubscriptions(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $notSubbedMag = $this->getMagazineByName('someother', $this->getUserByUsername('JaneDoe'));
        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:subscribe');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazineId']);
        // Block scope not granted
        self::assertTrue($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCannotRetrieveUserMagazineSubscriptionsAnonymous(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('testUser');
        $client->request('GET', "/api/users/{$user->getId()}/magazines/subscriptions");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveUserMagazineSubscriptionsWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $user = $this->getUserByUsername('testUser');
        $client->request(
            'GET',
            "/api/users/{$user->getId()}/magazines/subscriptions",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveUserMagazineSubscriptions(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $user = $this->getUserByUsername('testUser');
        $user->showProfileSubscriptions = true;
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $notSubbedMag = $this->getMagazineByName('someother', $this->getUserByUsername('JaneDoe'));
        $magazine = $this->getMagazineByName('test', $user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:subscribe');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/users/{$user->getId()}/magazines/subscriptions",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazineId']);
        // Block scope not granted
        self::assertFalse($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCannotRetrieveUserMagazineSubscriptionsIfSettingTurnedOff(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $user = $this->getUserByUsername('testUser');
        $user->showProfileSubscriptions = false;
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:subscribe');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/users/{$user->getId()}/magazines/subscriptions",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveModeratedMagazinesAnonymous(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/magazines/moderated');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveModeratedMagazinesWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines/moderated', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveModeratedMagazines(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $notModdedMag = $this->getMagazineByName('someother', $this->getUserByUsername('JaneDoe'));
        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:list');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines/moderated', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazineId']);
        // Subscribe and block scopes not granted
        self::assertNull($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCannotRetrieveBlockedMagazinesAnonymous(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/magazines/blocked');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveBlockedMagazinesWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines/blocked', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveBlockedMagazines(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $notBlockedMag = $this->getMagazineByName('someother', $this->getUserByUsername('JaneDoe'));
        $magazine = $this->getMagazineByName('test', $this->getUserByUsername('JaneDoe'));

        $magazineBlock = $this->getService(MagazineBlock::class);
        $magazineBlock($magazine, $this->getUserByUsername('JohnDoe'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write magazine:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/magazines/blocked', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(self::MAGAZINE_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazineId']);
        // Subscribe and block scopes not granted
        self::assertNull($jsonData['items'][0]['isUserSubscribed']);
        self::assertTrue($jsonData['items'][0]['isBlockedByUser']);
    }

    public static function assertAllValuesFoundByName(array $magazines, array $values, string $message = '')
    {
        $nameMap = array_column($magazines, null, 'name');
        $containsMagazine = fn (bool $result, array $item) => $result && null !== $nameMap[$item['name']];
        self::assertTrue(array_reduce($values, $containsMagazine, true), $message);
    }
}
