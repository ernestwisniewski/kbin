<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User;

use App\Repository\UserRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserRetrieveApiTest extends WebTestCase
{
    public const USER_SETTINGS_KEYS = [
        'notifyOnNewEntry',
        'notifyOnNewEntryReply',
        'notifyOnNewEntryCommentReply',
        'notifyOnNewPost',
        'notifyOnNewPostReply',
        'notifyOnNewPostCommentReply',
        'hideAdult',
        'turboMode',
        'showProfileSubscriptions',
        'showProfileFollowings',
        'addMentionsEntries',
        'addMentionsPosts',
        'homepage',
        'featuredMagazines',
        'preferredLanguages',
        'customCss',
        'ignoreMagazinesCustomCss',
    ];
    public const NUM_USERS = 10;

    public function testApiCanRetrieveUsersWithAboutAnonymous(): void
    {
        $client = self::createClient();

        $users = [];
        for ($i = 0; $i < self::NUM_USERS; ++$i) {
            $users[] = $this->getUserByUsername('user'.(string) ($i + 1), about: 'Test user '.(string) ($i + 1));
        }

        $client->request('GET', '/api/users');
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(self::NUM_USERS, $jsonData['pagination']['count']);
        self::assertSame(1, $jsonData['pagination']['currentPage']);
        self::assertSame(1, $jsonData['pagination']['maxPage']);
        // Default perPage count should be used since no perPage value was specified
        self::assertSame(UserRepository::PER_PAGE, $jsonData['pagination']['perPage']);

        self::assertIsArray($jsonData['items']);
        self::assertSame(self::NUM_USERS, \count($jsonData['items']));
    }

    public function testApiCanRetrieveUsersWithAbout(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('UserWithoutAbout'));
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $users = [];
        for ($i = 0; $i < self::NUM_USERS; ++$i) {
            $users[] = $this->getUserByUsername('user'.(string) ($i + 1), about: 'Test user '.(string) ($i + 1));
        }

        $client->request('GET', '/api/users', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(self::NUM_USERS, $jsonData['pagination']['count']);
    }

    public function testApiCanRetrieveUserByIdAnonymous(): void
    {
        $client = self::createClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');

        $client->request('GET', '/api/users/'.(string) $testUser->getId());
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        self::assertSame($testUser->getId(), $jsonData['userId']);
        self::assertSame('UserWithoutAbout', $jsonData['username']);
        self::assertNull($jsonData['about']);
        self::assertNotNull($jsonData['createdAt']);
        self::assertFalse($jsonData['isBot']);
        self::assertNull($jsonData['apId']);
        // Follow and block scopes not assigned, so these flags should be null
        self::assertNull($jsonData['isFollowedByUser']);
        self::assertNull($jsonData['isFollowerOfUser']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveUserById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/users/'.(string) $testUser->getId(), server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        self::assertSame($testUser->getId(), $jsonData['userId']);
        self::assertSame('UserWithoutAbout', $jsonData['username']);
        self::assertNull($jsonData['about']);
        self::assertNotNull($jsonData['createdAt']);
        self::assertFalse($jsonData['isBot']);
        self::assertNull($jsonData['apId']);
        // Follow and block scopes not assigned, so these flags should be null
        self::assertNull($jsonData['isFollowedByUser']);
        self::assertNull($jsonData['isFollowerOfUser']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveUserByNameAnonymous(): void
    {
        $client = self::createClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');

        $client->request('GET', '/api/users/name/'.$testUser->getUsername());
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        self::assertSame($testUser->getId(), $jsonData['userId']);
        self::assertSame('UserWithoutAbout', $jsonData['username']);
        self::assertNull($jsonData['about']);
        self::assertNotNull($jsonData['createdAt']);
        self::assertFalse($jsonData['isBot']);
        self::assertNull($jsonData['apId']);
        // Follow and block scopes not assigned, so these flags should be null
        self::assertNull($jsonData['isFollowedByUser']);
        self::assertNull($jsonData['isFollowerOfUser']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveUserByName(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/users/name/'.$testUser->getUsername(), server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        self::assertSame($testUser->getId(), $jsonData['userId']);
        self::assertSame('UserWithoutAbout', $jsonData['username']);
        self::assertNull($jsonData['about']);
        self::assertNotNull($jsonData['createdAt']);
        self::assertFalse($jsonData['isBot']);
        self::assertNull($jsonData['apId']);
        // Follow and block scopes not assigned, so these flags should be null
        self::assertNull($jsonData['isFollowedByUser']);
        self::assertNull($jsonData['isFollowerOfUser']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCannotRetrieveCurrentUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/users/me', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveCurrentUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:profile:read');

        $client->request('GET', '/api/users/me', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        self::assertSame($testUser->getId(), $jsonData['userId']);
        self::assertSame('UserWithoutAbout', $jsonData['username']);
        self::assertNull($jsonData['about']);
        self::assertNotNull($jsonData['createdAt']);
        self::assertFalse($jsonData['isBot']);
        self::assertNull($jsonData['apId']);
        // Follow and block scopes not assigned, so these flags should be null
        self::assertNull($jsonData['isFollowedByUser']);
        self::assertNull($jsonData['isFollowerOfUser']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveUserFlagsWithScopes(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $follower = $this->getUserByUsername('follower');

        $follower->follow($testUser);

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($follower);
        $manager->flush();

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/'.(string) $follower->getId(), server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);
        // Follow and block scopes assigned, so these flags should not be null
        self::assertFalse($jsonData['isFollowedByUser']);
        self::assertTrue($jsonData['isFollowerOfUser']);
        self::assertFalse($jsonData['isBlockedByUser']);
    }

    public function testApiCanGetBlockedUsers(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $blockedUser = $this->getUserByUsername('JohnDoe');

        $testUser->block($blockedUser);

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($testUser);
        $manager->flush();

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/blocked', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertSame(1, \count($jsonData['items']));
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($blockedUser->getId(), $jsonData['items'][0]['userId']);
    }

    public function testApiCannotGetFollowedUsersWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/users/followed', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotGetFollowersWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/users/followers', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetFollowedUsers(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $followedUser = $this->getUserByUsername('JohnDoe');

        $testUser->follow($followedUser);

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($testUser);
        $manager->flush();

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/followed', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertSame(1, \count($jsonData['items']));
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($followedUser->getId(), $jsonData['items'][0]['userId']);
    }

    public function testApiCanGetFollowers(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $followingUser = $this->getUserByUsername('JohnDoe');

        $followingUser->follow($testUser);

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($testUser);
        $manager->flush();

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/followers', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertSame(1, \count($jsonData['items']));
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($followingUser->getId(), $jsonData['items'][0]['userId']);
    }

    public function testApiCannotGetFollowedUsersByIdIfNotShared(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $followedUser = $this->getUserByUsername('JohnDoe');

        $testUser->follow($followedUser);
        $testUser->showProfileFollowings = false;

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($testUser);
        $manager->flush();

        $client->loginUser($followedUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/'.(string) $testUser->getId().'/followed', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetFollowedUsersById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $followedUser = $this->getUserByUsername('JohnDoe');

        $testUser->follow($followedUser);

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($testUser);
        $manager->flush();

        $client->loginUser($followedUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/'.(string) $testUser->getId().'/followed', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertSame(1, \count($jsonData['items']));
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($followedUser->getId(), $jsonData['items'][0]['userId']);
    }

    public function testApiCanGetFollowersById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $followingUser = $this->getUserByUsername('JohnDoe');

        $followingUser->follow($testUser);

        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($testUser);
        $manager->flush();

        $client->loginUser($followingUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/'.(string) $testUser->getId().'/followers', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertSame(1, \count($jsonData['items']));
        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($followingUser->getId(), $jsonData['items'][0]['userId']);
    }

    public function testApiCannotGetSettingsWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('JohnDoe');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/users/settings', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetSettings(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('JohnDoe');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:profile:read');

        $client->request('GET', '/api/users/settings', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::USER_SETTINGS_KEYS, $jsonData);
    }
}
