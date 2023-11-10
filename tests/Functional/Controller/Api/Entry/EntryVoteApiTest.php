<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry;

use App\Service\VoteManager;
use App\Tests\WebTestCase;

class EntryVoteApiTest extends WebTestCase
{
    public function testApiCannotUpvoteEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', magazine: $magazine);

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/1");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpvoteEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/1", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpvoteEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/1", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($entry->title, $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($entry->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($entry->lang, $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(1, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertFalse($jsonData['isFavourited']);
        self::assertSame(1, $jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotDownvoteEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', magazine: $magazine);

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/-1");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDownvoteEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/-1", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDownvoteEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/-1", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($entry->title, $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($entry->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($entry->lang, $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(1, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertFalse($jsonData['isFavourited']);
        self::assertSame(-1, $jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotClearVoteEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', magazine: $magazine);

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/0");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotClearVoteEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', user: $user, magazine: $magazine);

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $entry, $user, rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/0", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanClearVoteEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for upvote', user: $user, magazine: $magazine);

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $entry, $user, rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}/vote/0", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($entry->title, $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($entry->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($entry->lang, $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertFalse($jsonData['isFavourited']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
