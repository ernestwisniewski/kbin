<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\OAuth2;

use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OAuth2ClientApiTest extends WebTestCase
{
    public const CLIENT_RESPONSE_KEYS = [
        'identifier',
        'secret',
        'name',
        'contactEmail',
        'description',
        'user',
        'redirectUris',
        'grants',
        'scopes',
        'image',
    ];

    public function testApiCanCreateWorkingClient(): void
    {
        $client = self::createClient();

        $requestData = [
            'name' => '/kbin API Created Test Client',
            'description' => 'An OAuth2 client for testing purposes, created via the API',
            'contactEmail' => 'test@kbin.test',
            'redirectUris' => [
                'https://localhost:3002',
            ],
            'grants' => [
                'authorization_code',
                'refresh_token',
            ],
            'scopes' => [
                'read',
                'write',
                'admin:oauth_clients:read',
            ],
        ];

        $client->jsonRequest('POST', '/api/client', $requestData);

        self::assertResponseIsSuccessful();

        $clientData = self::getJsonResponse($client);
        self::assertIsArray($clientData);
        self::assertArrayKeysMatch(self::CLIENT_RESPONSE_KEYS, $clientData);
        self::assertNotNull($clientData['identifier']);
        self::assertNotNull($clientData['secret']);
        self::assertEquals($requestData['name'], $clientData['name']);
        self::assertEquals($requestData['contactEmail'], $clientData['contactEmail']);
        self::assertEquals($requestData['description'], $clientData['description']);
        self::assertNull($clientData['user']);
        self::assertIsArray($clientData['redirectUris']);
        self::assertEquals($requestData['redirectUris'], $clientData['redirectUris']);
        self::assertIsArray($clientData['grants']);
        self::assertEquals($requestData['grants'], $clientData['grants']);
        self::assertIsArray($clientData['scopes']);
        self::assertEquals($requestData['scopes'], $clientData['scopes']);
        self::assertNull($clientData['image']);

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $jsonData = self::getAuthorizationCodeTokenResponse(
            $client,
            clientId: $clientData['identifier'],
            clientSecret: $clientData['secret'],
            redirectUri: $clientData['redirectUris'][0],
        );

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
    }

    public function testApiCanCreateWorkingClientWithImage(): void
    {
        $client = self::createClient();

        $requestData = [
            'name' => '/kbin API Created Test Client',
            'description' => 'An OAuth2 client for testing purposes, created via the API',
            'contactEmail' => 'test@kbin.test',
            'redirectUris' => [
                'https://localhost:3002',
            ],
            'grants' => [
                'authorization_code',
                'refresh_token',
            ],
            'scopes' => [
                'read',
                'write',
                'admin:oauth_clients:read',
            ],
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $client->request('POST', '/api/client-with-logo', $requestData, files: ['uploadImage' => $image]);

        self::assertResponseIsSuccessful();

        $clientData = self::getJsonResponse($client);
        self::assertIsArray($clientData);
        self::assertArrayKeysMatch(self::CLIENT_RESPONSE_KEYS, $clientData);
        self::assertNotNull($clientData['identifier']);
        self::assertNotNull($clientData['secret']);
        self::assertEquals($requestData['name'], $clientData['name']);
        self::assertEquals($requestData['contactEmail'], $clientData['contactEmail']);
        self::assertEquals($requestData['description'], $clientData['description']);
        self::assertNull($clientData['user']);
        self::assertIsArray($clientData['redirectUris']);
        self::assertEquals($requestData['redirectUris'], $clientData['redirectUris']);
        self::assertIsArray($clientData['grants']);
        self::assertEquals($requestData['grants'], $clientData['grants']);
        self::assertIsArray($clientData['scopes']);
        self::assertEquals($requestData['scopes'], $clientData['scopes']);
        self::assertisArray($clientData['image']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $clientData['image']);

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        self::runAuthorizationCodeFlowToConsentPage($client, 'read write', 'oauth2state', $clientData['identifier'], $clientData['redirectUris'][0]);

        self::assertSelectorExists('img.oauth-client-logo');
        $logo = $client->getCrawler()->filter('img.oauth-client-logo')->first();
        self::assertStringContainsString($clientData['image']['filePath'], $logo->attr('src'));

        self::runAuthorizationCodeFlowToRedirectUri($client, 'read write', 'yes', 'oauth2state', $clientData['identifier'], $clientData['redirectUris'][0]);

        $jsonData = self::runAuthorizationCodeTokenFlow($client, $clientData['identifier'], $clientData['secret'], $clientData['redirectUris'][0]);

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
    }

    public function testApiCanDeletePrivateClient(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $query = http_build_query([
            'client_id' => 'testclient',
            'client_secret' => 'testsecret',
        ]);

        $client->request('DELETE', '/api/client?'.$query);

        self::assertResponseStatusCodeSame(204);

        $jsonData = self::getAuthorizationCodeTokenResponse($client);

        self::assertResponseStatusCodeSame(401);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('error', $jsonData);
        self::assertEquals('invalid_client', $jsonData['error']);
        self::assertArrayHasKey('error_description', $jsonData);
        self::assertArrayHasKey('message', $jsonData);
    }

    public function testAdminApiCanAccessClientStats(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $jsonData = self::getAuthorizationCodeTokenResponse($client, scopes: 'admin:oauth_clients:read');

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('access_token', $jsonData);

        $token = 'Bearer '.$jsonData['access_token'];

        $query = http_build_query([
            'resolution' => 'day',
        ]);

        $client->request('GET', '/api/clients/stats?'.$query, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('data', $jsonData);
        self::assertIsArray($jsonData['data']);
        self::assertCount(1, $jsonData['data']);
        self::assertIsArray($jsonData['data'][0]);
        self::assertArrayHasKey('client', $jsonData['data'][0]);
        self::assertEquals('/kbin Test Client', $jsonData['data'][0]['client']);
        self::assertArrayHasKey('datetime', $jsonData['data'][0]);

        // If tests are run near midnight UTC we might get unlucky with a failure, but that
        // should be unlikely.
        $today = (new \DateTime())->setTime(0, 0)->format('Y-m-d H:i:s');

        self::assertEquals($today, $jsonData['data'][0]['datetime']);
        self::assertArrayHasKey('count', $jsonData['data'][0]);
        self::assertEquals(1, $jsonData['data'][0]['count']);
    }

    public function testAdminApiCannotAccessClientStatsWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $jsonData = self::getAuthorizationCodeTokenResponse($client);

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('access_token', $jsonData);

        $token = 'Bearer '.$jsonData['access_token'];

        $query = http_build_query([
            'resolution' => 'day',
        ]);

        $client->request('GET', '/api/clients/stats?'.$query, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);

        $jsonData = self::getJsonResponse($client);
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('type', $jsonData);
        self::assertEquals('https://tools.ietf.org/html/rfc2616#section-10', $jsonData['type']);
        self::assertArrayHasKey('title', $jsonData);
        self::assertEquals('An error occurred', $jsonData['title']);
        self::assertArrayHasKey('status', $jsonData);
        self::assertEquals(403, $jsonData['status']);
        self::assertArrayHasKey('detail', $jsonData);
    }

    public function testAdminApiCanAccessClientList(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $jsonData = self::getAuthorizationCodeTokenResponse($client, scopes: 'admin:oauth_clients:read');

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('access_token', $jsonData);

        $token = 'Bearer '.$jsonData['access_token'];

        $client->request('GET', '/api/clients', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('items', $jsonData);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayHasKey('identifier', $jsonData['items'][0]);
        self::assertArrayNotHasKey('secret', $jsonData['items'][0]);
        self::assertEquals('testclient', $jsonData['items'][0]['identifier']);
        self::assertArrayHasKey('name', $jsonData['items'][0]);
        self::assertEquals('/kbin Test Client', $jsonData['items'][0]['name']);
        self::assertArrayHasKey('contactEmail', $jsonData['items'][0]);
        self::assertEquals('test@kbin.test', $jsonData['items'][0]['contactEmail']);
        self::assertArrayHasKey('description', $jsonData['items'][0]);
        self::assertEquals('An OAuth2 client for testing purposes', $jsonData['items'][0]['description']);
        self::assertArrayHasKey('user', $jsonData['items'][0]);
        self::assertNull($jsonData['items'][0]['user']);
        self::assertArrayHasKey('active', $jsonData['items'][0]);
        self::assertEquals(true, $jsonData['items'][0]['active']);
        self::assertArrayHasKey('createdAt', $jsonData['items'][0]);
        self::assertNotNull($jsonData['items'][0]['createdAt']);
        self::assertArrayHasKey('redirectUris', $jsonData['items'][0]);
        self::assertIsArray($jsonData['items'][0]['redirectUris']);
        self::assertCount(1, $jsonData['items'][0]['redirectUris']);
        self::assertArrayHasKey('grants', $jsonData['items'][0]);
        self::assertIsArray($jsonData['items'][0]['grants']);
        self::assertCount(2, $jsonData['items'][0]['grants']);
        self::assertArrayHasKey('scopes', $jsonData['items'][0]);
        self::assertIsArray($jsonData['items'][0]['scopes']);

        self::assertArrayHasKey('pagination', $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayHasKey('count', $jsonData['pagination']);
        self::assertEquals(1, $jsonData['pagination']['count']);
        self::assertArrayHasKey('currentPage', $jsonData['pagination']);
        self::assertEquals(1, $jsonData['pagination']['currentPage']);
        self::assertArrayHasKey('maxPage', $jsonData['pagination']);
        self::assertEquals(1, $jsonData['pagination']['maxPage']);
        self::assertArrayHasKey('perPage', $jsonData['pagination']);
        self::assertEquals(15, $jsonData['pagination']['perPage']);
    }

    public function testAdminApiCannotAccessClientListWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $jsonData = self::getAuthorizationCodeTokenResponse($client);

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('access_token', $jsonData);

        $token = 'Bearer '.$jsonData['access_token'];

        $client->request('GET', '/api/clients', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('type', $jsonData);
        self::assertEquals('https://tools.ietf.org/html/rfc2616#section-10', $jsonData['type']);
        self::assertArrayHasKey('title', $jsonData);
        self::assertEquals('An error occurred', $jsonData['title']);
        self::assertArrayHasKey('status', $jsonData);
        self::assertEquals(403, $jsonData['status']);
        self::assertArrayHasKey('detail', $jsonData);
    }

    public function testAdminApiCanAccessClientByIdentifier(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $jsonData = self::getAuthorizationCodeTokenResponse($client, scopes: 'admin:oauth_clients:read');

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('access_token', $jsonData);

        $token = 'Bearer '.$jsonData['access_token'];

        $client->request('GET', '/api/clients/testclient', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('identifier', $jsonData);
        self::assertArrayNotHasKey('secret', $jsonData);
        self::assertEquals('testclient', $jsonData['identifier']);
        self::assertArrayHasKey('name', $jsonData);
        self::assertEquals('/kbin Test Client', $jsonData['name']);
        self::assertArrayHasKey('contactEmail', $jsonData);
        self::assertEquals('test@kbin.test', $jsonData['contactEmail']);
        self::assertArrayHasKey('description', $jsonData);
        self::assertEquals('An OAuth2 client for testing purposes', $jsonData['description']);
        self::assertArrayHasKey('user', $jsonData);
        self::assertNull($jsonData['user']);
        self::assertArrayHasKey('active', $jsonData);
        self::assertEquals(true, $jsonData['active']);
        self::assertArrayHasKey('createdAt', $jsonData);
        self::assertNotNull($jsonData['createdAt']);
        self::assertArrayHasKey('redirectUris', $jsonData);
        self::assertIsArray($jsonData['redirectUris']);
        self::assertCount(1, $jsonData['redirectUris']);
        self::assertArrayHasKey('grants', $jsonData);
        self::assertIsArray($jsonData['grants']);
        self::assertCount(2, $jsonData['grants']);
        self::assertArrayHasKey('scopes', $jsonData);
        self::assertIsArray($jsonData['scopes']);
    }

    public function testApiCanRevokeTokens(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $tokenData = self::getAuthorizationCodeTokenResponse($client, scopes: 'admin:oauth_clients:read');

        self::assertResponseIsSuccessful();
        self::assertIsArray($tokenData);
        self::assertArrayHasKey('access_token', $tokenData);
        self::assertArrayHasKey('refresh_token', $tokenData);

        $token = 'Bearer '.$tokenData['access_token'];

        $client->request('GET', '/api/clients/testclient', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();

        $client->request('POST', '/api/revoke', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);

        $client->request('GET', '/api/clients/testclient', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(401);

        $jsonData = self::getRefreshTokenResponse($client, $tokenData['refresh_token']);

        self::assertResponseStatusCodeSame(401);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('error', $jsonData);
        self::assertEquals('invalid_request', $jsonData['error']);
        self::assertArrayHasKey('error_description', $jsonData);
        self::assertEquals('The refresh token is invalid.', $jsonData['error_description']);
        self::assertArrayHasKey('hint', $jsonData);
        self::assertEquals('Token has been revoked', $jsonData['hint']);
        self::assertArrayHasKey('message', $jsonData);
        self::assertEquals('The refresh token is invalid.', $jsonData['message']);
    }

    public function testAdminApiCannotAccessClientByIdentifierWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', isAdmin: true));
        self::createOAuth2AuthCodeClient();

        $jsonData = self::getAuthorizationCodeTokenResponse($client);

        self::assertResponseIsSuccessful();
        self::assertIsArray($jsonData);
        self::assertArrayHasKey('access_token', $jsonData);

        $token = 'Bearer '.$jsonData['access_token'];

        $client->request('GET', '/api/clients/testclient', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayHasKey('type', $jsonData);
        self::assertEquals('https://tools.ietf.org/html/rfc2616#section-10', $jsonData['type']);
        self::assertArrayHasKey('title', $jsonData);
        self::assertEquals('An error occurred', $jsonData['title']);
        self::assertArrayHasKey('status', $jsonData);
        self::assertEquals(403, $jsonData['status']);
        self::assertArrayHasKey('detail', $jsonData);
    }
}
