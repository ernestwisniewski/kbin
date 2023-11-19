<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Domain;

use App\Kbin\Domain\DomainBlock;
use App\Kbin\Domain\DomainSubscribe;
use App\Tests\WebTestCase;

class DomainRetrieveApiTest extends WebTestCase
{
    public function testApiCanRetrieveDomainsAnonymous()
    {
        $client = self::createClient();

        $this->getEntryByTitle('Test link to a domain', 'https://example.com');

        $client->request('GET', '/api/domains');
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('example.com', $jsonData['items'][0]['name']);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertSame(0, $jsonData['items'][0]['subscriptionsCount']);
        self::assertNull($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCanRetrieveDomains()
    {
        $client = self::createClient();

        $this->getEntryByTitle('Test link to a domain', 'https://example.com');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/domains', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('example.com', $jsonData['items'][0]['name']);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertSame(0, $jsonData['items'][0]['subscriptionsCount']);
        // Scope not granted so subscription and block flags not populated
        self::assertNull($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCanRetrieveDomainsSubscriptionAndBlockStatus()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainSubscribe = $this->getService(DomainSubscribe::class);
        $domainSubscribe($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read domain:subscribe domain:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/domains', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('example.com', $jsonData['items'][0]['name']);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertSame(1, $jsonData['items'][0]['subscriptionsCount']);
        // Scope granted so subscription and block flags populated
        self::assertTrue($jsonData['items'][0]['isUserSubscribed']);
        self::assertFalse($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCannotRetrieveSubscribedDomainsAnonymous()
    {
        $client = self::createClient();

        $client->request('GET', '/api/domains/subscribed');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveSubscribedDomainsWithoutScope()
    {
        $client = self::createClient();

        $this->getEntryByTitle('Test link to a second domain', 'https://example.org');
        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainSubscribe = $this->getService(DomainSubscribe::class);
        $domainSubscribe($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/domains/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveSubscribedDomains()
    {
        $client = self::createClient();

        $this->getEntryByTitle('Test link to a second domain', 'https://example.org');
        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainSubscribe = $this->getService(DomainSubscribe::class);
        $domainSubscribe($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read domain:subscribe');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/domains/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertEquals('example.com', $jsonData['items'][0]['name']);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertSame(1, $jsonData['items'][0]['subscriptionsCount']);
        // Scope granted so subscription flag populated
        self::assertTrue($jsonData['items'][0]['isUserSubscribed']);
        self::assertNull($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCannotRetrieveBlockedDomainsAnonymous()
    {
        $client = self::createClient();

        $client->request('GET', '/api/domains/blocked');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveBlockedDomainsWithoutScope()
    {
        $client = self::createClient();

        $this->getEntryByTitle('Test link to a second domain', 'https://example.org');
        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainBlock = $this->getService(DomainBlock::class);
        $domainBlock($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/domains/blocked', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveBlockedDomains()
    {
        $client = self::createClient();

        $this->getEntryByTitle('Test link to a second domain', 'https://example.org');
        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainBlock = $this->getService(DomainBlock::class);
        $domainBlock($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read domain:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/domains/blocked', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertEquals('example.com', $jsonData['items'][0]['name']);
        self::assertSame(1, $jsonData['items'][0]['entryCount']);
        self::assertSame(0, $jsonData['items'][0]['subscriptionsCount']);
        // Scope granted so block flag populated
        self::assertNull($jsonData['items'][0]['isUserSubscribed']);
        self::assertTrue($jsonData['items'][0]['isBlockedByUser']);
    }

    public function testApiCanRetrieveDomainByIdAnonymous()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;

        $client->request('GET', "/api/domain/{$domain->getId()}");
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(0, $jsonData['subscriptionsCount']);
        self::assertNull($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveDomainById()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainSubscribe = $this->getService(DomainSubscribe::class);
        $domainSubscribe($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/domain/{$domain->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(1, $jsonData['subscriptionsCount']);
        // Scope not granted so subscription and block flags not populated
        self::assertNull($jsonData['isUserSubscribed']);
        self::assertNull($jsonData['isBlockedByUser']);
    }

    public function testApiCanRetrieveDomainByIdSubscriptionAndBlockStatus()
    {
        $client = self::createClient();

        $domain = $this->getEntryByTitle('Test link to a domain', 'https://example.com')->domain;
        $user = $this->getUserByUsername('JohnDoe');
        $domainSubscribe = $this->getService(DomainSubscribe::class);
        $domainSubscribe($domain, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);
        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read domain:subscribe domain:block');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/domain/{$domain->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();

        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData);
        self::assertEquals('example.com', $jsonData['name']);
        self::assertSame(1, $jsonData['entryCount']);
        self::assertSame(1, $jsonData['subscriptionsCount']);
        // Scope granted so subscription and block flags populated
        self::assertTrue($jsonData['isUserSubscribed']);
        self::assertFalse($jsonData['isBlockedByUser']);
    }
}
