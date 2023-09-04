<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Instance;

use App\Tests\WebTestCase;

class InstanceDetailsApiTest extends WebTestCase
{
    public const INSTANCE_PAGE_RESPONSE_KEYS = ['about', 'contact', 'faq', 'privacyPolicy', 'terms'];

    public function testApiCanRetrieveInstanceDetailsAnonymous(): void
    {
        $client = self::createClient();
        $site = $this->createInstancePages();

        $client->request('GET', '/api/instance');

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_PAGE_RESPONSE_KEYS, $jsonData);
        self::assertEquals($site->about, $jsonData['about']);
        self::assertEquals($site->contact, $jsonData['contact']);
        self::assertEquals($site->faq, $jsonData['faq']);
        self::assertEquals($site->privacyPolicy, $jsonData['privacyPolicy']);
        self::assertEquals($site->terms, $jsonData['terms']);
    }

    public function testApiCanRetrieveInstanceDetails(): void
    {
        $client = self::createClient();
        $site = $this->createInstancePages();

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/instance', server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertArrayKeysMatch(self::INSTANCE_PAGE_RESPONSE_KEYS, $jsonData);
        self::assertEquals($site->about, $jsonData['about']);
        self::assertEquals($site->contact, $jsonData['contact']);
        self::assertEquals($site->faq, $jsonData['faq']);
        self::assertEquals($site->privacyPolicy, $jsonData['privacyPolicy']);
        self::assertEquals($site->terms, $jsonData['terms']);
    }
}
