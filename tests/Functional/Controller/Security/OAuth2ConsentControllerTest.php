<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Tests\WebTestCase;

class OAuth2ConsentControllerTest extends WebTestCase
{
    public function testUserCanConsent(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        self::runAuthorizationCodeFlowToConsentPage($client, 'read write', 'oauth2state');

        self::assertSelectorTextContains("li[id='oauth2.grant.read.general']", 'Read all content you have access to.');
        self::assertSelectorTextContains("li[id='oauth2.grant.write.general']", 'Create or edit any of your threads, posts, or comments.');

        self::runAuthorizationCodeFlowToRedirectUri($client, 'read write', 'yes', 'oauth2state');

        $response = $client->getResponse();

        $parsedUrl = parse_url($response->headers->get('Location'));
        self::assertEquals('https', $parsedUrl['scheme']);
        self::assertEquals('localhost', $parsedUrl['host']);
        self::assertEquals('3001', $parsedUrl['port']);
        self::assertStringContainsString('code', $parsedUrl['query']);
    }

    public function testUserCanDissent(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        self::runAuthorizationCodeFlowToConsentPage($client, 'read write', 'oauth2state');

        self::assertSelectorTextContains("li[id='oauth2.grant.read.general']", 'Read all content you have access to.');
        self::assertSelectorTextContains("li[id='oauth2.grant.write.general']", 'Create or edit any of your threads, posts, or comments.');

        self::runAuthorizationCodeFlowToRedirectUri($client, 'read write', 'no', 'oauth2state');

        $response = $client->getResponse();

        $parsedUrl = parse_url($response->headers->get('Location'));
        self::assertEquals('https', $parsedUrl['scheme']);
        self::assertEquals('localhost', $parsedUrl['host']);
        self::assertEquals('3001', $parsedUrl['port']);
        self::assertStringContainsString('error=access_denied', $parsedUrl['query']);
    }
}
