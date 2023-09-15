<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntryCommentRetrieveApiTest extends WebTestCase
{
    public function testApiCanGetEntryCommentsAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $this->createEntryComment("test parent comment {$i}", $entry);
        }

        $client->request('GET', "/api/entry/{$entry->getId()}/comments");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(5, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(5, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(0, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertEmpty($comment['children']);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCannotGetEntryCommentsByPreferredLangAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $this->createEntryComment("test parent comment {$i}", $entry);
        }

        $client->request('GET', "/api/entry/{$entry->getId()}/comments?usePreferredLangs=true");
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetEntryCommentsByPreferredLang(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $this->createEntryComment("test parent comment {$i}", $entry);
            $this->createEntryComment("test german parent comment {$i}", $entry, lang: 'de');
            $this->createEntryComment("test dutch parent comment {$i}", $entry, lang: 'nl');
        }

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('user');
        $user->preferredLanguages = ['en', 'de'];

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}/comments?usePreferredLangs=true", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(10, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(10, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('parent comment', $comment['body']);
            self::assertTrue('en' === $comment['lang'] || 'de' === $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(0, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertEmpty($comment['children']);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            // No scope granted so these should be null
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCanGetEntryCommentsWithLanguageAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $this->createEntryComment("test parent comment {$i}", $entry);
            $this->createEntryComment("test german parent comment {$i}", $entry, lang: 'de');
            $this->createEntryComment("test dutch comment {$i}", $entry, lang: 'nl');
        }

        $client->request('GET', "/api/entry/{$entry->getId()}/comments?lang[]=en&lang[]=de");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(10, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(10, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('parent comment', $comment['body']);
            self::assertTrue('en' === $comment['lang'] || 'de' === $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(0, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertEmpty($comment['children']);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCanGetEntryCommentsWithLanguage(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $this->createEntryComment("test parent comment {$i}", $entry);
            $this->createEntryComment("test german parent comment {$i}", $entry, lang: 'de');
            $this->createEntryComment("test dutch parent comment {$i}", $entry, lang: 'nl');
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}/comments?lang[]=en&lang[]=de", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(10, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(10, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('parent comment', $comment['body']);
            self::assertTrue('en' === $comment['lang'] || 'de' === $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(0, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertEmpty($comment['children']);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            // No scope granted so these should be null
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCanGetEntryComments(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $this->createEntryComment("test parent comment {$i}", $entry);
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}/comments", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(5, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(5, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(0, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertEmpty($comment['children']);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            // No scope granted so these should be null
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCanGetEntryCommentsWithChildren(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 5; ++$i) {
            $comment = $this->createEntryComment("test parent comment {$i}", $entry);
            $this->createEntryComment("test child comment {$i}", $entry, parent: $comment);
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}/comments", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(5, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(5, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(1, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertCount(1, $comment['children']);
            self::assertIsArray($comment['children'][0]);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment['children'][0]);
            self::assertStringContainsString('test child comment', $comment['children'][0]['body']);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            // No scope granted so these should be null
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCanGetEntryCommentsLimitedDepth(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        for ($i = 0; $i < 2; ++$i) {
            $comment = $this->createEntryComment("test parent comment {$i}", $entry);
            $parent = $comment;
            for ($j = 1; $j <= 5; ++$j) {
                $parent = $this->createEntryComment("test child comment {$i} depth {$j}", $entry, parent: $parent);
            }
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}/comments?d=3", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($entry->getId(), $comment['entryId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['dv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(5, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertCount(1, $comment['children']);
            $depth = 0;
            $current = $comment;
            while (\count($current['children']) > 0) {
                self::assertIsArray($current['children'][0]);
                self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $current['children'][0]);
                self::assertStringContainsString('test child comment', $current['children'][0]['body']);
                self::assertSame(5 - ($depth + 1), $current['children'][0]['childCount']);
                $current = $current['children'][0];
                ++$depth;
            }
            self::assertSame(3, $depth);
            self::assertFalse($comment['isAdult']);
            self::assertNull($comment['image']);
            self::assertNull($comment['parentId']);
            self::assertNull($comment['rootId']);
            // No scope granted so these should be null
            self::assertNull($comment['isFavourited']);
            self::assertNull($comment['userVote']);
            self::assertNull($comment['apId']);
            self::assertNull($comment['tags']);
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['createdAt'], 'createdAt date format invalid');
            self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $comment['lastActive'], 'lastActive date format invalid');
        }
    }

    public function testApiCanGetEntryCommentByIdAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        $comment = $this->createEntryComment('test parent comment', $entry);

        $client->request('GET', "/api/comments/{$comment->getId()}");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertStringContainsString('test parent comment', $jsonData['body']);
        self::assertSame('en', $jsonData['lang']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(0, $jsonData['childCount']);
        self::assertSame('visible', $jsonData['visibility']);
        self::assertIsArray($jsonData['mentions']);
        self::assertEmpty($jsonData['mentions']);
        self::assertIsArray($jsonData['children']);
        self::assertEmpty($jsonData['children']);
        self::assertFalse($jsonData['isAdult']);
        self::assertNull($jsonData['image']);
        self::assertNull($jsonData['parentId']);
        self::assertNull($jsonData['rootId']);
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertNull($jsonData['apId']);
        self::assertNull($jsonData['tags']);
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
    }

    public function testApiCanGetEntryCommentById(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        $comment = $this->createEntryComment('test parent comment', $entry);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/comments/{$comment->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertStringContainsString('test parent comment', $jsonData['body']);
        self::assertSame('en', $jsonData['lang']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(0, $jsonData['childCount']);
        self::assertSame('visible', $jsonData['visibility']);
        self::assertIsArray($jsonData['mentions']);
        self::assertEmpty($jsonData['mentions']);
        self::assertIsArray($jsonData['children']);
        self::assertEmpty($jsonData['children']);
        self::assertFalse($jsonData['isAdult']);
        self::assertNull($jsonData['image']);
        self::assertNull($jsonData['parentId']);
        self::assertNull($jsonData['rootId']);
        // No scope granted so these should be null
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertNull($jsonData['apId']);
        self::assertNull($jsonData['tags']);
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
    }

    public function testApiCanGetEntryCommentByIdWithDepth(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('test entry', body: 'test');
        $comment = $this->createEntryComment('test parent comment', $entry);
        $parent = $comment;
        for ($i = 0; $i < 5; ++$i) {
            $parent = $this->createEntryComment('test nested reply', $entry, parent: $parent);
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/comments/{$comment->getId()}?d=2", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertStringContainsString('test parent comment', $jsonData['body']);
        self::assertSame('en', $jsonData['lang']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(5, $jsonData['childCount']);
        self::assertSame('visible', $jsonData['visibility']);
        self::assertIsArray($jsonData['mentions']);
        self::assertEmpty($jsonData['mentions']);
        self::assertIsArray($jsonData['children']);
        self::assertCount(1, $jsonData['children']);
        self::assertFalse($jsonData['isAdult']);
        self::assertNull($jsonData['image']);
        self::assertNull($jsonData['parentId']);
        self::assertNull($jsonData['rootId']);
        // No scope granted so these should be null
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertNull($jsonData['apId']);
        self::assertNull($jsonData['tags']);
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');

        $depth = 0;
        $current = $jsonData;
        while (\count($current['children']) > 0) {
            self::assertIsArray($current['children'][0]);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $current['children'][0]);
            ++$depth;
            $current = $current['children'][0];
        }

        self::assertSame(2, $depth);
    }
}
