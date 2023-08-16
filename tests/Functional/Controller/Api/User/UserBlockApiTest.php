<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserBlockApiTest extends WebTestCase
{
    public function testApiCannotBlockUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $blockedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('PUT', '/api/users/'.(string) $blockedUser->getId().'/block', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUnblockUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $blockedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('PUT', '/api/users/'.(string) $blockedUser->getId().'/unblock', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanBlockUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout');
        $followedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('PUT', '/api/users/'.(string) $followedUser->getId().'/block', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('userId', $jsonData);
        self::assertArrayHasKey('username', $jsonData);
        self::assertArrayHasKey('about', $jsonData);
        self::assertArrayHasKey('avatar', $jsonData);
        self::assertArrayHasKey('cover', $jsonData);
        self::assertArrayNotHasKey('lastActive', $jsonData);
        self::assertArrayHasKey('createdAt', $jsonData);
        self::assertArrayHasKey('followersCount', $jsonData);
        self::assertArrayHasKey('apId', $jsonData);
        self::assertArrayHasKey('apProfileId', $jsonData);
        self::assertArrayHasKey('isBot', $jsonData);
        self::assertArrayHasKey('isFollowedByUser', $jsonData);
        self::assertArrayHasKey('isFollowerOfUser', $jsonData);
        self::assertArrayHasKey('isBlockedByUser', $jsonData);

        self::assertSame(0, $jsonData['followersCount']);
        self::assertFalse($jsonData['isFollowedByUser']);
        self::assertFalse($jsonData['isFollowerOfUser']);
        self::assertTrue($jsonData['isBlockedByUser']);
    }

    public function testApiCanUnblockUser(): void
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

        $client->request('PUT', '/api/users/'.(string) $blockedUser->getId().'/unblock', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('userId', $jsonData);
        self::assertArrayHasKey('username', $jsonData);
        self::assertArrayHasKey('about', $jsonData);
        self::assertArrayHasKey('avatar', $jsonData);
        self::assertArrayHasKey('cover', $jsonData);
        self::assertArrayNotHasKey('lastActive', $jsonData);
        self::assertArrayHasKey('createdAt', $jsonData);
        self::assertArrayHasKey('followersCount', $jsonData);
        self::assertArrayHasKey('apId', $jsonData);
        self::assertArrayHasKey('apProfileId', $jsonData);
        self::assertArrayHasKey('isBot', $jsonData);
        self::assertArrayHasKey('isFollowedByUser', $jsonData);
        self::assertArrayHasKey('isFollowerOfUser', $jsonData);
        self::assertArrayHasKey('isBlockedByUser', $jsonData);

        self::assertSame(0, $jsonData['followersCount']);
        self::assertFalse($jsonData['isFollowedByUser']);
        self::assertFalse($jsonData['isFollowerOfUser']);
        self::assertFalse($jsonData['isBlockedByUser']);
    }
}
