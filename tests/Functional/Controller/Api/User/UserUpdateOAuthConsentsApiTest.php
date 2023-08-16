<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User;

use App\Tests\WebTestCase;

class UserUpdateOAuthConsentsApiTest extends WebTestCase
{
    public function testApiCannotUpdateConsentsWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(UserRetrieveOAuthConsentsApiTest::CONSENT_RESPONSE_KEYS, $jsonData['items'][0]);

        $client->jsonRequest(
            'PUT', '/api/users/consents/'.(string) $jsonData['items'][0]['consentId'],
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateConsents(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:oauth_clients:edit user:follow');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(UserRetrieveOAuthConsentsApiTest::CONSENT_RESPONSE_KEYS, $jsonData['items'][0]);

        self::assertEquals([
            'read',
            'user:oauth_clients:read',
            'user:oauth_clients:edit',
            'user:follow',
        ], $jsonData['items'][0]['scopesGranted']);

        $client->jsonRequest(
            'PUT', '/api/users/consents/'.(string) $jsonData['items'][0]['consentId'],
            parameters: ['scopes' => [
                'read',
                'user:oauth_clients:read',
                'user:oauth_clients:edit',
            ]],
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);
        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(UserRetrieveOAuthConsentsApiTest::CONSENT_RESPONSE_KEYS, $jsonData);
        self::assertEquals([
            'read',
            'user:oauth_clients:read',
            'user:oauth_clients:edit',
        ], $jsonData['scopesGranted']);
    }

    public function testApiUpdatingConsentsDoesNotAffectExistingKeys(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:oauth_clients:edit user:follow');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);
        $client->jsonRequest(
            'PUT', '/api/users/consents/'.(string) $jsonData['items'][0]['consentId'],
            parameters: ['scopes' => [
                'read',
                'user:oauth_clients:edit',
            ]],
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        // Existing token still has permission to read oauth consents despite client consent being revoked.
        $client->jsonRequest(
            'GET', '/api/users/consents/'.(string) $jsonData['consentId'],
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);
        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(UserRetrieveOAuthConsentsApiTest::CONSENT_RESPONSE_KEYS, $jsonData);
        self::assertEquals([
            'read',
            'user:oauth_clients:edit',
        ], $jsonData['scopesGranted']);
    }

    public function testApiCannotAddConsents(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:oauth_clients:edit user:follow');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertCount(1, $jsonData['items']);
        self::assertArrayKeysMatch(UserRetrieveOAuthConsentsApiTest::CONSENT_RESPONSE_KEYS, $jsonData['items'][0]);

        self::assertEquals([
            'read',
            'user:oauth_clients:read',
            'user:oauth_clients:edit',
            'user:follow',
        ], $jsonData['items'][0]['scopesGranted']);

        $client->jsonRequest(
            'PUT', '/api/users/consents/'.(string) $jsonData['items'][0]['consentId'],
            parameters: ['scopes' => [
                'read',
                'user:oauth_clients:read',
                'user:oauth_clients:edit',
                'user:block',
            ]],
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseStatusCodeSame(403);
    }
}
