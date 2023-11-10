<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Instance\Admin;

use App\Tests\WebTestCase;

class InstanceSettingsUpdateApiTest extends WebTestCase
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
        'KBIN_HEADER_LOGO',
        'KBIN_CAPTCHA_ENABLED',
        'KBIN_SPAM_PROTECTION',
        'KBIN_MERCURE_ENABLED',
        'KBIN_FEDERATION_PAGE_ENABLED',
        'KBIN_ADMIN_ONLY_OAUTH_CLIENTS',
        'KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN',
    ];

    public function testApiCannotUpdateInstanceSettingsAnonymous(): void
    {
        $client = self::createClient();

        $client->request('PUT', '/api/instance/settings');

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateInstanceSettingsWithoutAdmin(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/instance/settings', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateInstanceSettingsWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', '/api/instance/settings', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateInstanceSettings(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe', isAdmin: true);
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:instance:settings:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $settings = [
            'KBIN_DOMAIN' => 'kbinupdated.test',
            'KBIN_TITLE' => 'updated title',
            'KBIN_META_TITLE' => 'meta title',
            'KBIN_META_KEYWORDS' => 'this, is, a, test',
            'KBIN_META_DESCRIPTION' => 'Testing out the API',
            'KBIN_DEFAULT_LANG' => 'de',
            'KBIN_CONTACT_EMAIL' => 'test@kbinupdated.test',
            'KBIN_SENDER_EMAIL' => 'noreply@kbinupdated.test',
            'KBIN_JS_ENABLED' => true,
            'KBIN_FEDERATION_ENABLED' => true,
            'KBIN_REGISTRATIONS_ENABLED' => false,
            'KBIN_BANNED_INSTANCES' => ['test.social'],
            'KBIN_HEADER_LOGO' => true,
            'KBIN_CAPTCHA_ENABLED' => true,
            'KBIN_SPAM_PROTECTION' => false,
            'KBIN_MERCURE_ENABLED' => false,
            'KBIN_FEDERATION_PAGE_ENABLED' => false,
            'KBIN_ADMIN_ONLY_OAUTH_CLIENTS' => true,
            'KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN' => false,
        ];

        $client->jsonRequest('PUT', '/api/instance/settings', $settings, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_SETTINGS_RESPONSE_KEYS, $jsonData);
        foreach ($jsonData as $key => $value) {
            self::assertEquals($settings[$key], $value, "$key did not match!");
        }

        $settings = [
            'KBIN_DOMAIN' => 'kbin.test',
            'KBIN_TITLE' => 'updated title',
            'KBIN_META_TITLE' => 'meta title',
            'KBIN_META_KEYWORDS' => 'this, is, a, test',
            'KBIN_META_DESCRIPTION' => 'Testing out the API',
            'KBIN_DEFAULT_LANG' => 'en',
            'KBIN_CONTACT_EMAIL' => 'test@kbinupdated.test',
            'KBIN_SENDER_EMAIL' => 'noreply@kbinupdated.test',
            'KBIN_JS_ENABLED' => false,
            'KBIN_FEDERATION_ENABLED' => false,
            'KBIN_REGISTRATIONS_ENABLED' => true,
            'KBIN_BANNED_INSTANCES' => ['test.social'],
            'KBIN_HEADER_LOGO' => false,
            'KBIN_CAPTCHA_ENABLED' => false,
            'KBIN_SPAM_PROTECTION' => false,
            'KBIN_MERCURE_ENABLED' => true,
            'KBIN_FEDERATION_PAGE_ENABLED' => true,
            'KBIN_ADMIN_ONLY_OAUTH_CLIENTS' => false,
            'KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN' => true,
        ];

        $client->jsonRequest('PUT', '/api/instance/settings', $settings, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
    }
}
