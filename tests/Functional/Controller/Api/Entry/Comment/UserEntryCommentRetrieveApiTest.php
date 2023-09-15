<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment;

use App\Service\FavouriteManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserEntryCommentRetrieveApiTest extends WebTestCase
{
    public function testApiCanGetUserEntryCommentsAnonymous(): void
    {
        $client = self::createClient();
        $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry);
        $user = $entry->user;

        $client->request('GET', "/api/users/{$user->getId()}/comments");
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('test comment', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertSame(0, $jsonData['items'][0]['childCount']);
        self::assertIsArray($jsonData['items'][0]['children']);
        self::assertEmpty($jsonData['items'][0]['children']);
        self::assertSame($comment->getId(), $jsonData['items'][0]['commentId']);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
    }

    public function testApiCanGetUserEntryComments(): void
    {
        $client = self::createClient();
        $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry);
        $user = $entry->user;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$user->getId()}/comments", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('test comment', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertSame(0, $jsonData['items'][0]['childCount']);
        self::assertIsArray($jsonData['items'][0]['children']);
        self::assertEmpty($jsonData['items'][0]['children']);
        self::assertSame($comment->getId(), $jsonData['items'][0]['commentId']);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
    }

    public function testApiCanGetUserEntryCommentsDepth(): void
    {
        $client = self::createClient();
        $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry);
        $nested1 = $this->createEntryComment('test comment nested 1', $entry, parent: $comment);
        $nested2 = $this->createEntryComment('test comment nested 2', $entry, parent: $nested1);
        $nested3 = $this->createEntryComment('test comment nested 3', $entry, parent: $nested2);
        $user = $entry->user;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$user->getId()}/comments?d=2", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(4, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(4, $jsonData['pagination']['count']);

        foreach ($jsonData['items'] as $comment) {
            self::assertIsArray($comment);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $comment);
            self::assertTrue(\count($comment['children']) <= 1);
            $depth = 0;
            $current = $comment;
            while (\count($current['children']) > 0) {
                ++$depth;
                $current = $current['children'][0];
                self::assertIsArray($current);
                self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $current);
            }
            self::assertTrue($depth <= 2);
        }
    }

    public function testApiCanGetUserEntryCommentsNewest(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('entry', url: 'https://google.com');
        $first = $this->createEntryComment('first', $entry);
        $second = $this->createEntryComment('second', $entry);
        $third = $this->createEntryComment('third', $entry);
        $user = $entry->user;

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

        $client->request('GET', "/api/users/{$user->getId()}/comments?sort=newest", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['commentId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['commentId']);
    }

    public function testApiCanGetUserEntryCommentsOldest(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('entry', url: 'https://google.com');
        $first = $this->createEntryComment('first', $entry);
        $second = $this->createEntryComment('second', $entry);
        $third = $this->createEntryComment('third', $entry);
        $user = $entry->user;

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

        $client->request('GET', "/api/users/{$user->getId()}/comments?sort=oldest", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['commentId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['commentId']);
    }

    public function testApiCanGetUserEntryCommentsActive(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('entry', url: 'https://google.com');
        $first = $this->createEntryComment('first', $entry);
        $second = $this->createEntryComment('second', $entry);
        $third = $this->createEntryComment('third', $entry);
        $user = $entry->user;

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

        $client->request('GET', "/api/users/{$user->getId()}/comments?sort=active", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['commentId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['commentId']);
    }

    public function testApiCanGetUserEntryCommentsTop(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('entry', url: 'https://google.com');
        $first = $this->createEntryComment('first', $entry);
        $second = $this->createEntryComment('second', $entry);
        $third = $this->createEntryComment('third', $entry);
        $user = $entry->user;

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle($this->getUserByUsername('voter1'), $first);
        $favouriteManager->toggle($this->getUserByUsername('voter2'), $first);
        $favouriteManager->toggle($this->getUserByUsername('voter1'), $second);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$user->getId()}/comments?sort=top", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['commentId']);
        self::assertSame(2, $jsonData['items'][0]['favourites']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);
        self::assertSame(1, $jsonData['items'][1]['favourites']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['commentId']);
        self::assertSame(0, $jsonData['items'][2]['favourites']);
    }

    public function testApiCanGetUserEntryCommentsHot(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('entry', url: 'https://google.com');
        $first = $this->createEntryComment('first', $entry);
        $second = $this->createEntryComment('second', $entry);
        $third = $this->createEntryComment('third', $entry);
        $user = $entry->user;

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter1'), rateLimit: false);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter2'), rateLimit: false);
        $voteManager->vote(1, $second, $this->getUserByUsername('voter1'), rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$user->getId()}/comments?sort=hot", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['commentId']);
        self::assertSame(2, $jsonData['items'][0]['uv']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);
        self::assertSame(1, $jsonData['items'][1]['uv']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['commentId']);
        self::assertSame(0, $jsonData['items'][2]['uv']);
    }

    public function testApiCanGetUserEntryCommentsWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry);
        $user = $entry->user;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/users/{$user->getId()}/comments", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($comment->getId(), $jsonData['items'][0]['commentId']);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('test comment', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertSame(0, $jsonData['items'][0]['childCount']);
        self::assertIsArray($jsonData['items'][0]['children']);
        self::assertEmpty($jsonData['items'][0]['children']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertFalse($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertNull($jsonData['items'][0]['apId']);
    }
}
