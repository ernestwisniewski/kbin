<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User\Admin;

use App\Kbin\User\UserDelete;
use App\Repository\UserRepository;
use App\Tests\WebTestCase;

class UserDeleteApiTest extends WebTestCase
{
    public function testApiCannotDeleteUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $deletedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request(
            'DELETE',
            '/api/admin/users/'.(string) $deletedUser->getId().'/delete_account',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $deletedUser = $repository->find($deletedUser->getId());
        self::assertFalse($deletedUser->isAccountDeleted());
    }

    public function testApiCannotDeleteUserWithoutAdminAccount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: false);
        $deletedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:delete');

        $client->request(
            'DELETE',
            '/api/admin/users/'.(string) $deletedUser->getId().'/delete_account',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $deletedUser = $repository->find($deletedUser->getId());
        self::assertFalse($deletedUser->isAccountDeleted());
    }

    public function testApiCanDeleteUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $deletedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:delete');

        $client->request(
            'DELETE',
            '/api/admin/users/'.(string) $deletedUser->getId().'/delete_account',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        $repository = $this->getService(UserRepository::class);
        $deletedUser = $repository->find($deletedUser->getId());
        self::assertTrue($deletedUser->isAccountDeleted());
    }

    public function testDeleteApiReturns404IfUserNotFound(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $deletedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:delete');

        $client->request(
            'DELETE',
            '/api/admin/users/'.(string) ($deletedUser->getId() * 10).'/delete_account',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(404);

        $repository = $this->getService(UserRepository::class);
        $deletedUser = $repository->find($deletedUser->getId());
        self::assertFalse($deletedUser->isBanned);
    }

    public function testDeleteApiReturns401IfTokenNotProvided(): void
    {
        $client = self::createClient();
        $deletedUser = $this->getUserByUsername('JohnDoe');

        $client->request('DELETE', '/api/admin/users/'.(string) $deletedUser->getId().'/delete_account');
        self::assertResponseStatusCodeSame(401);
    }

    public function testDeleteApiIsIdempotent(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $deletedUser = $this->getUserByUsername('JohnDoe');

        ($this->getService(UserDelete::class))($deletedUser);

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:delete');

        // Ban user a second time with the API
        $client->request(
            'DELETE',
            '/api/admin/users/'.(string) $deletedUser->getId().'/delete_account',
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::USER_RESPONSE_KEYS, $jsonData);

        $repository = $this->getService(UserRepository::class);
        $deletedUser = $repository->find($deletedUser->getId());
        self::assertTrue($deletedUser->isAccountDeleted());
    }
}
