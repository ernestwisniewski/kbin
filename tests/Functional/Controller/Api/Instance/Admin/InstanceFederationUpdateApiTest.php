<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Instance\Admin;

use App\Service\SettingsManager;
use App\Tests\WebTestCase;

class InstanceFederationUpdateApiTest extends WebTestCase
{
    public function testApiCannotUpdateInstanceFederationAnonymous(): void
    {
        $client = self::createClient();

        $client->request('PUT', '/api/defederated');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateInstanceFederationWithoutAdmin(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/defederated', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateInstanceFederationWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/defederated', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateInstanceFederation(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:federation:update');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', '/api/defederated', ['instances' => ['bad-instance.com']], server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(['instances'], $jsonData);
        self::assertSame(['bad-instance.com'], $jsonData['instances']);
    }

    public function testApiCanClearInstanceFederation(): void
    {
        $client = self::createClient();

        $manager = $this->getService(SettingsManager::class);
        $manager->set('KBIN_BANNED_INSTANCES', ['defederated.social', 'evil.social']);

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:federation:update');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', '/api/defederated', ['instances' => []], server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(['instances'], $jsonData);
        self::assertEmpty($jsonData['instances']);
    }
}
