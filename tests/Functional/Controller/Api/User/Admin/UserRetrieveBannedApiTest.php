<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User\Admin;

use App\Service\UserManager;
use App\Tests\WebTestCase;

class UserRetrieveBannedApiTest extends WebTestCase
{
    public function testApiCannotRetrieveBannedUsersWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $this->getService(UserManager::class)->ban($bannedUser);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');

        $client->request('GET', '/api/admin/users/banned', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveBannedUsersWithoutAdminAccount(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: false);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $this->getService(UserManager::class)->ban($bannedUser);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request('GET', '/api/admin/users/banned', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveBannedUsers(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('UserWithoutAbout', isAdmin: true);
        $bannedUser = $this->getUserByUsername('JohnDoe');
        $this->getService(UserManager::class)->ban($bannedUser);
        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:user:ban');

        $client->request('GET', '/api/admin/users/banned', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);
        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(array_merge(self::USER_RESPONSE_KEYS, ['isBanned']), $jsonData['items'][0]);
        self::assertSame($bannedUser->getId(), $jsonData['items'][0]['userId']);
    }
}
