<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry;

use App\Service\VoteManager;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserEntryRetrieveApiTest extends WebTestCase
{
    public function testApiCanGetUserEntriesAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $otherUser = $this->getUserByUsername('somebody');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine, user: $otherUser);

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('another entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertSame($otherUser->getId(), $jsonData['items'][0]['user']['userId']);
        self::assertEquals('link', $jsonData['items'][0]['type']);
        self::assertSame(0, $jsonData['items'][0]['numComments']);
    }

    public function testApiCanGetUserEntries(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $otherUser = $this->getUserByUsername('somebody');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine, user: $otherUser);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('another entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertSame($otherUser->getId(), $jsonData['items'][0]['user']['userId']);
        self::assertEquals('link', $jsonData['items'][0]['type']);
        self::assertSame(0, $jsonData['items'][0]['numComments']);
    }

    public function testApiCanGetUserEntriesNewest(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');
        $otherUser = $first->user;

        $first->createdAt = new \DateTimeImmutable('-1 hour');
        $second->createdAt = new \DateTimeImmutable('-1 second');
        $third->createdAt = new \DateTimeImmutable();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($first);
        $entityManager->persist($second);
        $entityManager->persist($third);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries?sort=newest", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['entryId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['entryId']);
    }

    public function testApiCanGetUserEntriesOldest(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');
        $otherUser = $first->user;

        $first->createdAt = new \DateTimeImmutable('-1 hour');
        $second->createdAt = new \DateTimeImmutable('-1 second');
        $third->createdAt = new \DateTimeImmutable();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($first);
        $entityManager->persist($second);
        $entityManager->persist($third);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries?sort=oldest", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['entryId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['entryId']);
    }

    public function testApiCanGetUserEntriesCommented(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $this->createEntryComment('comment 1', $first);
        $this->createEntryComment('comment 2', $first);
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $this->createEntryComment('comment 1', $second);
        $third = $this->getEntryByTitle('third', url: 'https://google.com');
        $otherUser = $first->user;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries?sort=commented", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['entryId']);
        self::assertSame(2, $jsonData['items'][0]['numComments']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);
        self::assertSame(1, $jsonData['items'][1]['numComments']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['entryId']);
        self::assertSame(0, $jsonData['items'][2]['numComments']);
    }

    public function testApiCanGetUserEntriesActive(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');
        $otherUser = $first->user;

        $first->lastActive = new \DateTime('-1 hour');
        $second->lastActive = new \DateTime('-1 second');
        $third->lastActive = new \DateTime();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($first);
        $entityManager->persist($second);
        $entityManager->persist($third);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries?sort=active", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['entryId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['entryId']);
    }

    public function testApiCanGetUserEntriesTop(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');
        $otherUser = $first->user;

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter1'), rateLimit: false);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter2'), rateLimit: false);
        $voteManager->vote(1, $second, $this->getUserByUsername('voter1'), rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries?sort=top", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['entryId']);
        self::assertSame(2, $jsonData['items'][0]['uv']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);
        self::assertSame(1, $jsonData['items'][1]['uv']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['entryId']);
        self::assertSame(0, $jsonData['items'][2]['uv']);
    }

    public function testApiCanGetUserEntriesWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $this->getEntryByTitle('an entry', body: 'test');
        $otherUser = $this->getUserByUsername('somebody');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine, user: $otherUser);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$otherUser->getId()}/entries", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('another entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertSame($otherUser->getId(), $jsonData['items'][0]['user']['userId']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertEquals('https://google.com', $jsonData['items'][0]['url']);
        self::assertNull($jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertSame(0, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertFalse($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('link', $jsonData['items'][0]['type']);
        self::assertEquals('another-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }
}
