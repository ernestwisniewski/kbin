<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Instance\Admin;

use App\Tests\WebTestCase;

class InstanceSettingsRetrieveApiTest extends WebTestCase
{
    public const INSTANCE_SETTINGS_RESPONSE_KEYS = [
        'KBIN_DOMAIN',
        'KBIN_TITLE',
        'KBIN_META_TITLE',
        'KBIN_META_KEYWORDS',
        'KBIN_META_DESCRIPTION',
        'KBIN_DEFAULT_LANG',
        'KBIN_CONTACT_EMAIL',
        'KBIN_SENDER_EMAIL',
        'KBIN_JS_ENABLED',
        'KBIN_FEDERATION_ENABLED',
        'KBIN_REGISTRATIONS_ENABLED',
        'KBIN_BANNED_INSTANCES',
        'KBIN_BLOCKED_INSTANCES',
        'KBIN_HEADER_LOGO',
        'KBIN_CAPTCHA_ENABLED',
        'KBIN_SPAM_PROTECTION',
        'KBIN_MERCURE_ENABLED',
        'KBIN_FEDERATION_PAGE_ENABLED',
        'KBIN_ADMIN_ONLY_OAUTH_CLIENTS',
        'KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN',
    ];

    public function testApiCannotRetrieveInstanceSettingsAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/instance/settings');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveInstanceSettingsWithoutAdmin(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/instance/settings', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveInstanceSettingsWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/instance/settings', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveInstanceSettings(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:instance:settings:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/instance/settings', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_SETTINGS_RESPONSE_KEYS, $jsonData);
        foreach ($jsonData as $key => $value) {
            self::assertNotNull($value, "$key was null!");
        }
    }
}
