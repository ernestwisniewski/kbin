<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Instance;

use App\Service\SettingsManager;
use App\Tests\WebTestCase;

class InstanceFederationApiTest extends WebTestCase
{
    public const INSTANCE_DEFEDERATED_RESPONSE_KEYS = ['instances'];

    public function testApiCanRetrieveEmptyInstanceDefederation(): void
    {
        $client = self::createClient();
        $settings = $this->getService(SettingsManager::class);
        $settings->set('KBIN_BANNED_INSTANCES', []);

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/defederated', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_DEFEDERATED_RESPONSE_KEYS, $jsonData);
        self::assertSame([], $jsonData['instances']);
    }

    public function testApiCanRetrieveInstanceDefederationAnonymous(): void
    {
        $client = self::createClient();
        $settings = $this->getService(SettingsManager::class);
        $settings->set('KBIN_BANNED_INSTANCES', ['defederated.social']);

        $client->request('GET', '/api/defederated');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_DEFEDERATED_RESPONSE_KEYS, $jsonData);
        self::assertSame(['defederated.social'], $jsonData['instances']);
    }

    public function testApiCanRetrieveInstanceDefederation(): void
    {
        $client = self::createClient();
        $settings = $this->getService(SettingsManager::class);
        $settings->set('KBIN_BANNED_INSTANCES', ['defederated.social', 'evil.social']);

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/defederated', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_DEFEDERATED_RESPONSE_KEYS, $jsonData);
        self::assertSame(['defederated.social', 'evil.social'], $jsonData['instances']);
    }
}
