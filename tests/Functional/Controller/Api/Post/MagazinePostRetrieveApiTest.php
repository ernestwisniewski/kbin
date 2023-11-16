<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post;

use App\Kbin\Post\PostPin;
use App\Service\VoteManager;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class MagazinePostRetrieveApiTest extends WebTestCase
{
    public function testApiCanGetMagazinePostsAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine);

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts");
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('another post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);

        self::assertSame(0, $jsonData['items'][0]['comments']);
    }

    public function testApiCanGetMagazinePosts(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('another post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertSame(0, $jsonData['items'][0]['comments']);
    }

    public function testApiCanGetMagazinePostsPinnedFirst(): void
    {
        $client = self::createClient();
        $voteManager = $this->getService(VoteManager::class);
        $postPin = $this->getService(PostPin::class);
        $voter = $this->getUserByUsername('voter');
        $first = $this->createPost('a post');
        $this->createPostComment('up the ranking', $first);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->createPost('another post', magazine: $magazine);
        // Upvote and comment on $second so it should come first, but then pin $third so it actually comes first
        $voteManager->vote(1, $second, $voter, rateLimit: false);
        $this->createPostComment('test', $second, $voter);
        $third = $this->createPost('a pinned post', magazine: $magazine);
        $postPin($third);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals('a pinned post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);

        self::assertSame(0, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertTrue($jsonData['items'][0]['isPinned']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertSame(1, $jsonData['items'][1]['comments']);
        self::assertSame(1, $jsonData['items'][1]['uv']);
        self::assertFalse($jsonData['items'][1]['isPinned']);
    }

    public function testApiCanGetMagazinePostsNewest(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');
        $magazine = $first->magazine;

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

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts?sort=newest", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['postId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['postId']);
    }

    public function testApiCanGetMagazinePostsOldest(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');
        $magazine = $first->magazine;

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

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts?sort=oldest", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['postId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['postId']);
    }

    public function testApiCanGetMagazinePostsCommented(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $this->createPostComment('comment 1', $first);
        $this->createPostComment('comment 2', $first);
        $second = $this->createPost('second');
        $this->createPostComment('comment 1', $second);
        $third = $this->createPost('third');
        $magazine = $first->magazine;

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts?sort=commented", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['postId']);
        self::assertSame(2, $jsonData['items'][0]['comments']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);
        self::assertSame(1, $jsonData['items'][1]['comments']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['postId']);
        self::assertSame(0, $jsonData['items'][2]['comments']);
    }

    public function testApiCanGetMagazinePostsActive(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');
        $magazine = $first->magazine;

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

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts?sort=active", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['postId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['postId']);
    }

    public function testApiCanGetMagazinePostsTop(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');
        $magazine = $first->magazine;

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter1'), rateLimit: false);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter2'), rateLimit: false);
        $voteManager->vote(1, $second, $this->getUserByUsername('voter1'), rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts?sort=top", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['postId']);
        self::assertSame(2, $jsonData['items'][0]['uv']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);
        self::assertSame(1, $jsonData['items'][1]['uv']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['postId']);
        self::assertSame(0, $jsonData['items'][2]['uv']);
    }

    public function testApiCanGetMagazinePostsWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $first = $this->createPost('an post');
        $this->createPostComment('up the ranking', $first);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $post = $this->createPost('another post', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}/posts", server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('another post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(0, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertFalse($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('another-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }
}
