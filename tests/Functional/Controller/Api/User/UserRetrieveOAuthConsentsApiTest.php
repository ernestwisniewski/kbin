<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\User;

use App\DTO\OAuth2ClientDto;
use App\Tests\WebTestCase;

class UserRetrieveOAuthConsentsApiTest extends WebTestCase
{
    public const CONSENT_RESPONSE_KEYS = [
        'consentId',
        'client',
        'description',
        'clientLogo',
        'scopesGranted',
        'scopesAvailable',
    ];

    public function testApiCannotGetConsentsWithoutScope(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:follow user:block');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetConsents(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:follow user:block');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items']);
        self::assertSame(1, count($jsonData['items']));

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::CONSENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals(
            ['read', 'user:oauth_clients:read', 'user:follow', 'user:block'],
            $jsonData['items'][0]['scopesGranted']
        );
        self::assertEquals(
            OAuth2ClientDto::AVAILABLE_SCOPES,
            $jsonData['items'][0]['scopesAvailable']
        );
        self::assertEquals('/kbin Test Client', $jsonData['items'][0]['client']);
        self::assertEquals('An OAuth2 client for testing purposes', $jsonData['items'][0]['description']);
        self::assertNull($jsonData['items'][0]['clientLogo']);
    }

    public function testApiCannotGetOtherUsersConsentsById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');
        $testUser2 = $this->getUserByUsername('someuser2');

        $client->loginUser($testUser);
        $codes1 = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:follow user:block');

        $client->loginUser($testUser2);
        $codes2 = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:follow user:block');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes1['token_type'].' '.$codes1['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertSame(1, count($jsonData['items']));

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::CONSENT_RESPONSE_KEYS, $jsonData['items'][0]);

        $client->request(
            'GET', '/api/users/consents/'.(string) $jsonData['items'][0]['consentId'],
            server: ['HTTP_AUTHORIZATION' => $codes2['token_type'].' '.$codes2['access_token']]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetConsentsById(): void
    {
        $client = self::createClient();
        self::createOAuth2AuthCodeClient();
        $testUser = $this->getUserByUsername('someuser');

        $client->loginUser($testUser);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read user:oauth_clients:read user:follow user:block');

        $client->request('GET', '/api/users/consents', server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertSame(1, count($jsonData['items']));

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::CONSENT_RESPONSE_KEYS, $jsonData['items'][0]);

        $consent = $jsonData['items'][0];

        $client->request(
            'GET', '/api/users/consents/'.(string) $consent['consentId'],
            server: ['HTTP_AUTHORIZATION' => $codes['token_type'].' '.$codes['access_token']]
        );
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertEquals($consent, $jsonData);
    }
}
