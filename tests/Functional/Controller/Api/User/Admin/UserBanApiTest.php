<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User\Admin;

use App\Kbin\User\UserBan;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;

class UserBanApiTest extends WebTestCase
{
    public function testApiCannotBanUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/ban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertFalse($bannedUser->isBanned);
    }

    public function testApiCannotUnbanUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        ($this->getService(UserBan::class))($bannedUser);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/unban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertTrue($bannedUser->isBanned);
    }

    public function testApiCannotBanUserWithoutAdminAccount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: false);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/ban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertFalse($bannedUser->isBanned);
    }

    public function testApiCannotUnbanUserWithoutAdminAccount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: false);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        ($this->getService(UserBan::class))($bannedUser);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/unban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertTrue($bannedUser->isBanned);
    }

    public function testApiCanBanUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/ban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(array_merge(self::USER_RESPONSE_KEYS, ['isBanned']), $jsonData);
        self::assertTrue($jsonData['isBanned']);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertTrue($bannedUser->isBanned);
    }

    public function testApiCanUnbanUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');

        ($this->getService(UserBan::class))($bannedUser);

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/unban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(array_merge(self::USER_RESPONSE_KEYS, ['isBanned']), $jsonData);
        self::assertFalse($jsonData['isBanned']);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertFalse($bannedUser->isBanned);
    }

    public function testBanApiReturns404IfUserNotFound(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) ($bannedUser->getId() * 10).'/ban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(404);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertFalse($bannedUser->isBanned);
    }

    public function testUnbanApiReturns404IfUserNotFound(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');

        ($this->getService(UserBan::class))($bannedUser);

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) ($bannedUser->getId() * 10).'/unban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(404);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertTrue($bannedUser->isBanned);
    }

    public function testBanApiReturns401IfTokenNotProvided(): void
    {
        $client = self::createClient();
        $bannedUser = $this->getUserByUsername('JohnDoe');

        $client->request('POST', '/api/admin/users/'.(string) $bannedUser->getId().'/ban');
        self::assertResponseStatusCodeSame(401);
    }

    public function testUnbanApiReturns401IfTokenNotProvided(): void
    {
        $client = self::createClient();
        $bannedUser = $this->getUserByUsername('JohnDoe');

        $client->request('POST', '/api/admin/users/'.(string) $bannedUser->getId().'/unban');
        self::assertResponseStatusCodeSame(401);
    }

    public function testBanApiIsIdempotent(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');

        ($this->getService(UserBan::class))($bannedUser);

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        // Ban user a second time with the API
        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/ban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(array_merge(self::USER_RESPONSE_KEYS, ['isBanned']), $jsonData);
        self::assertTrue($jsonData['isBanned']);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertTrue($bannedUser->isBanned);
    }

    public function testUnbanApiIsIdempotent(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');

        // Do not ban user

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request(
            'POST',
            '/api/admin/users/'.(string) $bannedUser->getId().'/unban',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(array_merge(self::USER_RESPONSE_KEYS, ['isBanned']), $jsonData);
        self::assertFalse($jsonData['isBanned']);

        $repository = $this->getService(UserRepository::class);
        $bannedUser = $repository->find($bannedUser->getId());
        self::assertFalse($bannedUser->isBanned);
    }
}
