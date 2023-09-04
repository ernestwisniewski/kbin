<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User\Admin;

use App\Repository\UserRepository;
use App\Tests\WebTestCase;

class UserVerifyApiTest extends WebTestCase
{
    public function testApiCannotVerifyUserWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $unverifiedUser = $this->getUserByUsername('JohnDoe', active: false);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('PUT', '/api/admin/users/'.(string) $unverifiedUser->getId().'/verify', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $unverifiedUser = $repository->find($unverifiedUser->getId());
        self::assertFalse($unverifiedUser->isVerified);
    }

    public function testApiCannotVerifyUserWithoutAdminAccount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: false);
        $unverifiedUser = $this->getUserByUsername('JohnDoe', active: false);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:verify');

        $client->request('PUT', '/api/admin/users/'.(string) $unverifiedUser->getId().'/verify', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);

        $repository = $this->getService(UserRepository::class);
        $unverifiedUser = $repository->find($unverifiedUser->getId());
        self::assertFalse($unverifiedUser->isVerified);
    }

    public function testApiCanVerifyUser(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $unverifiedUser = $this->getUserByUsername('JohnDoe', active: false);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:verify');

        $client->request('PUT', '/api/admin/users/'.(string) $unverifiedUser->getId().'/verify', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(200);

        $jsonData = self::getJsonResponse($client);
        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(array_merge(self::USER_RESPONSE_KEYS, ['isVerified']), $jsonData);
        self::assertTrue($jsonData['isVerified']);

        $repository = $this->getService(UserRepository::class);
        $unverifiedUser = $repository->find($unverifiedUser->getId());
        self::assertTrue($unverifiedUser->isVerified);
    }

    public function testVerifyApiReturns404IfUserNotFound(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $unverifiedUser = $this->getUserByUsername('JohnDoe', active: false);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:verify');

        $client->request('PUT', '/api/admin/users/'.(string) ($unverifiedUser->getId() * 10).'/verify', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(404);
    }

    public function testVerifyApiReturns401IfTokenNotProvided(): void
    {
        $client = self::createClient();
        $unverifiedUser = $this->getUserByUsername('JohnDoe', active: false);

        $client->request('PUT', '/api/admin/users/'.(string) $unverifiedUser->getId().'/verify');
        self::assertResponseStatusCodeSame(401);
    }
}
