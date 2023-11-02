<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User\Admin;

use App\Repository\UserRepository;
use App\Tests\WebTestCase;

class UserPurgeApiTest extends WebTestCase
{
    public function testApiCannotPurgeUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $purgedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('DELETE', '/api/admin/users/'.(string) $purgedUser->getId().'/purge_account', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $purgedUser = $repository->find($purgedUser->getId());
        self::assertNotNull($purgedUser);
    }

    public function testApiCannotPurgeUserWithoutAdminAccount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: false);
        $purgedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:purge');

        $client->request('DELETE', '/api/admin/users/'.(string) $purgedUser->getId().'/purge_account', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);

        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $purgedUser = $repository->find($purgedUser->getId());
        self::assertNotNull($purgedUser);
    }

    public function testApiCanPurgeUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $purgedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:purge');

        $client->request('DELETE', '/api/admin/users/'.(string) $purgedUser->getId().'/purge_account', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(204);

        $repository = $this->getService(UserRepository::class);
        $purgedUser = $repository->find($purgedUser->getId());
        self::assertNull($purgedUser);
    }

    public function testPurgeApiReturns404IfUserNotFound(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $purgedUser = $this->getUserByUsername('JohnDoe');
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:purge');

        $client->request('DELETE', '/api/admin/users/'.(string) ($purgedUser->getId() * 10).'/purge_account', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(404);

        $repository = $this->getService(UserRepository::class);
        $purgedUser = $repository->find($purgedUser->getId());
        self::assertNotNull($purgedUser);
    }

    public function testPurgeApiReturns401IfTokenNotProvided(): void
    {
        $client = self::createClient();
        $purgedUser = $this->getUserByUsername('JohnDoe');

        $client->request('DELETE', '/api/admin/users/'.(string) $purgedUser->getId().'/purge_account');
        self::assertResponseStatusCodeSame(401);
    }
}
