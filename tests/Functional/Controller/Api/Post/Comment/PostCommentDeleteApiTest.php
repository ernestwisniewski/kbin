<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment;

use App\Repository\PostCommentRepository;
use App\Tests\WebTestCase;

class PostCommentDeleteApiTest extends WebTestCase
{
    public function testApiCannotDeleteCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $client->request('DELETE', "/api/post-comments/{$comment->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeleteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post-comments/{$comment->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotDeleteOtherUsersComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user2);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post-comments/{$comment->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeleteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $commentRepository = $this->getService(PostCommentRepository::class);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post-comments/{$comment->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(204);
        $comment = $commentRepository->find($comment->getId());
        self::assertNull($comment);
    }

    public function testApiCanSoftDeleteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);
        $this->createPostComment('test comment', $post, $user, parent: $comment);

        $commentRepository = $this->getService(PostCommentRepository::class);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post-comments/{$comment->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(204);
        $comment = $commentRepository->find($comment->getId());
        self::assertNotNull($comment);
        self::assertTrue($comment->isSoftDeleted());
    }
}
