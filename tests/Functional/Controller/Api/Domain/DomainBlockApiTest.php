<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Domain;

use App\Kbin\Domain\DomainBlock;
use App\Tests\WebTestCase;

class DomainBlockApiTest extends WebTestCase
{
    public function testApiCannotBlockDomainAnonymous()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;

        $client->request('PUT', "/api/domain/{$domain->getId()}/block");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotBlockDomainWithoutScope()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/domain/{$domain->getId()}/block", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanBlockDomain()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read domain:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/domain/{$domain->getId()}/block", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(DomainRetrieveApiTest::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(0, $jsonData['subscriptionsCount']);
        self::assertTrue($jsonData['isBlockedByUser']);
        // Scope not granted so subscribe flag not populated
        self::assertNull($jsonData['isUserSubscribed']);

        // Idempotent when called multiple times
        $client->request('PUT', "/api/domain/{$domain->getId()}/block", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(DomainRetrieveApiTest::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(0, $jsonData['subscriptionsCount']);
        self::assertTrue($jsonData['isBlockedByUser']);
        // Scope not granted so subscribe flag not populated
        self::assertNull($jsonData['isUserSubscribed']);
    }

    public function testApiCannotUnblockDomainAnonymous()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;

        $client->request('PUT', "/api/domain/{$domain->getId()}/unblock");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUnblockDomainWithoutScope()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/domain/{$domain->getId()}/unblock", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUnblockDomain()
    {
        $client = self::createClient();

        $user = $this->getUserByUsername('JohnDoe');
        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $domainBlock = $this->getService(DomainBlock::class);
        $domainBlock($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read domain:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/domain/{$domain->getId()}/unblock", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(DomainRetrieveApiTest::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(0, $jsonData['subscriptionsCount']);
        self::assertFalse($jsonData['isBlockedByUser']);
        // Scope not granted so subscribe flag not populated
        self::assertNull($jsonData['isUserSubscribed']);

        // Idempotent when called multiple times
        $client->request('PUT', "/api/domain/{$domain->getId()}/unblock", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(DomainRetrieveApiTest::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(0, $jsonData['subscriptionsCount']);
        self::assertFalse($jsonData['isBlockedByUser']);
        // Scope not granted so subscribe flag not populated
        self::assertNull($jsonData['isUserSubscribed']);
    }
}
