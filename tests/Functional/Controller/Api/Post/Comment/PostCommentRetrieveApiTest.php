<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment;

use App\Kbin\Vote\VoteCreate;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class PostCommentRetrieveApiTest extends WebTestCase
{
    public function testApiCanGetPostCommentsAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $this->createPostComment("test parent comment {$i}", $post);
        }

        $client->request('GET', "/api/posts/{$post->getId()}/comments");
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
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
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCannotGetPostCommentsByPreferredLangAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $this->createPostComment("test parent comment {$i}", $post);
        }

        $client->request('GET', "/api/posts/{$post->getId()}/comments?usePreferredLangs=true");
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetPostCommentsByPreferredLang(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $this->createPostComment("test parent comment {$i}", $post);
            $this->createPostComment("test german parent comment {$i}", $post, lang: 'de');
            $this->createPostComment("test dutch parent comment {$i}", $post, lang: 'nl');
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

        $client->request(
            'GET',
            "/api/posts/{$post->getId()}/comments?usePreferredLangs=true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('parent comment', $comment['body']);
            self::assertTrue('en' === $comment['lang'] || 'de' === $comment['lang']);
            self::assertSame(0, $comment['uv']);
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
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCanGetPostCommentsWithLanguageAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $this->createPostComment("test parent comment {$i}", $post);
            $this->createPostComment("test german parent comment {$i}", $post, lang: 'de');
            $this->createPostComment("test dutch comment {$i}", $post, lang: 'nl');
        }

        $client->request('GET', "/api/posts/{$post->getId()}/comments?lang[]=en&lang[]=de");
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('parent comment', $comment['body']);
            self::assertTrue('en' === $comment['lang'] || 'de' === $comment['lang']);
            self::assertSame(0, $comment['uv']);
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
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCanGetPostCommentsWithLanguage(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $this->createPostComment("test parent comment {$i}", $post);
            $this->createPostComment("test german parent comment {$i}", $post, lang: 'de');
            $this->createPostComment("test dutch parent comment {$i}", $post, lang: 'nl');
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/posts/{$post->getId()}/comments?lang[]=en&lang[]=de",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('parent comment', $comment['body']);
            self::assertTrue('en' === $comment['lang'] || 'de' === $comment['lang']);
            self::assertSame(0, $comment['uv']);
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
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCanGetPostComments(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $this->createPostComment("test parent comment {$i} #tag @user", $post);
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/posts/{$post->getId()}/comments", server: ['HTTP_AUTHORIZATION' => $token]);
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(0, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertSame(['@user'], $comment['mentions']);
            self::assertIsArray($comment['tags']);
            self::assertSame(['tag'], $comment['tags']);
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
            self::assertNull($comment['editedAt']);
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCanGetPostCommentsWithChildren(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 5; ++$i) {
            $comment = $this->createPostComment("test parent comment {$i}", $post);
            $this->createPostComment("test child comment {$i}", $post, parent: $comment);
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/posts/{$post->getId()}/comments", server: ['HTTP_AUTHORIZATION' => $token]);
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
            self::assertSame(0, $comment['favourites']);
            self::assertSame(1, $comment['childCount']);
            self::assertSame('visible', $comment['visibility']);
            self::assertIsArray($comment['mentions']);
            self::assertEmpty($comment['mentions']);
            self::assertIsArray($comment['children']);
            self::assertCount(1, $comment['children']);
            self::assertIsArray($comment['children'][0]);
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment['children'][0]);
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
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCanGetPostCommentsLimitedDepth(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        for ($i = 0; $i < 2; ++$i) {
            $comment = $this->createPostComment("test parent comment {$i}", $post);
            $parent = $comment;
            for ($j = 1; $j <= 5; ++$j) {
                $parent = $this->createPostComment("test child comment {$i} depth {$j}", $post, parent: $parent);
            }
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/posts/{$post->getId()}/comments?d=3", server: ['HTTP_AUTHORIZATION' => $token]);
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
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $comment);
            self::assertIsArray($comment['user']);
            self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $comment['user']);
            self::assertIsArray($comment['magazine']);
            self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $comment['magazine']);
            self::assertSame($post->getId(), $comment['postId']);
            self::assertStringContainsString('test parent comment', $comment['body']);
            self::assertSame('en', $comment['lang']);
            self::assertSame(0, $comment['uv']);
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
                self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $current['children'][0]);
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
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['createdAt'],
                'createdAt date format invalid'
            );
            self::assertStringMatchesFormat(
                '%d-%d-%dT%d:%d:%d%i:00',
                $comment['lastActive'],
                'lastActive date format invalid'
            );
        }
    }

    public function testApiCanGetPostCommentsNewest(): void
    {
        $client = self::createClient();
        $post = $this->createPost('post');
        $first = $this->createPostComment('first', $post);
        $second = $this->createPostComment('second', $post);
        $third = $this->createPostComment('third', $post);

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

        $client->request(
            'GET',
            "/api/posts/{$post->getId()}/comments?sort=newest",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
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
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['commentId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['commentId']);
    }

    public function testApiCanGetPostCommentsOldest(): void
    {
        $client = self::createClient();
        $post = $this->createPost('post');
        $first = $this->createPostComment('first', $post);
        $second = $this->createPostComment('second', $post);
        $third = $this->createPostComment('third', $post);

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

        $client->request(
            'GET',
            "/api/posts/{$post->getId()}/comments?sort=oldest",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
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
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['commentId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['commentId']);
    }

    public function testApiCanGetPostCommentsActive(): void
    {
        $client = self::createClient();
        $post = $this->createPost('post');
        $first = $this->createPostComment('first', $post);
        $second = $this->createPostComment('second', $post);
        $third = $this->createPostComment('third', $post);

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

        $client->request(
            'GET',
            "/api/posts/{$post->getId()}/comments?sort=active",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
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
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['commentId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['commentId']);
    }

    public function testApiCanGetPostCommentsHot(): void
    {
        $client = self::createClient();
        $post = $this->createPost('post');
        $first = $this->createPostComment('first', $post);
        $second = $this->createPostComment('second', $post);
        $third = $this->createPostComment('third', $post);

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(1, $first, $this->getUserByUsername('voter1'), rateLimit: false);
        $voteCreate(1, $first, $this->getUserByUsername('voter2'), rateLimit: false);
        $voteCreate(1, $second, $this->getUserByUsername('voter1'), rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/posts/{$post->getId()}/comments?sort=hot",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
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
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['commentId']);
        self::assertSame(2, $jsonData['items'][0]['uv']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['commentId']);
        self::assertSame(1, $jsonData['items'][1]['uv']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['commentId']);
        self::assertSame(0, $jsonData['items'][2]['uv']);
    }

    public function testApiCanGetPostCommentByIdAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        $comment = $this->createPostComment('test parent comment', $post);

        $client->request('GET', "/api/post-comments/{$comment->getId()}");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertStringContainsString('test parent comment', $jsonData['body']);
        self::assertSame('en', $jsonData['lang']);
        self::assertSame(0, $jsonData['uv']);
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
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['createdAt'],
            'createdAt date format invalid'
        );
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['lastActive'],
            'lastActive date format invalid'
        );
    }

    public function testApiCanGetPostCommentById(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        $comment = $this->createPostComment('test parent comment', $post);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/post-comments/{$comment->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertStringContainsString('test parent comment', $jsonData['body']);
        self::assertSame('en', $jsonData['lang']);
        self::assertSame(0, $jsonData['uv']);
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
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['createdAt'],
            'createdAt date format invalid'
        );
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['lastActive'],
            'lastActive date format invalid'
        );
    }

    public function testApiCanGetPostCommentByIdWithDepth(): void
    {
        $client = self::createClient();
        $post = $this->createPost('test post');
        $comment = $this->createPostComment('test parent comment', $post);
        $parent = $comment;
        for ($i = 0; $i < 5; ++$i) {
            $parent = $this->createPostComment('test nested reply', $post, parent: $parent);
        }

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/post-comments/{$comment->getId()}?d=2", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertStringContainsString('test parent comment', $jsonData['body']);
        self::assertSame('en', $jsonData['lang']);
        self::assertSame(0, $jsonData['uv']);
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
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['createdAt'],
            'createdAt date format invalid'
        );
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['lastActive'],
            'lastActive date format invalid'
        );

        $depth = 0;
        $current = $jsonData;
        while (\count($current['children']) > 0) {
            self::assertIsArray($current['children'][0]);
            self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $current['children'][0]);
            ++$depth;
            $current = $current['children'][0];
        }

        self::assertSame(2, $depth);
    }
}
