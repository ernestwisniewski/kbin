<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentUpdateApiTest extends WebTestCase
{
    public function testApiCannotUpdateCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/post-comments/{$comment->getId()}", $update);

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post-comments/{$comment->getId()}", $update, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateOtherUsersComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user2);

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post-comments/{$comment->getId()}", $update, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);
        $parent = $comment;
        for ($i = 0; $i < 5; ++$i) {
            $parent = $this->createPostComment('test reply', $post, $user, parent: $parent);
        }

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post-comments/{$comment->getId()}?d=2", $update, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment->getId(), $jsonData['commentId']);
        self::assertSame($update['body'], $jsonData['body']);
        self::assertSame($update['lang'], $jsonData['lang']);
        self::assertSame($update['isAdult'], $jsonData['isAdult']);
        self::assertSame(5, $jsonData['childCount']);

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
