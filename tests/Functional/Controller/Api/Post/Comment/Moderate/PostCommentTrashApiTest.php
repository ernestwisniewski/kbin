<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment\Moderate;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Tests\WebTestCase;

class PostCommentTrashApiTest extends WebTestCase
{
    public function testApiCannotTrashCommentAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post);

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/trash");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotTrashCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineManager = $this->getService(MagazineManager::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineManager->addModerator($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/trash", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotTrashComment(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/trash", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanTrashComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineManager = $this->getService(MagazineManager::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineManager->addModerator($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/trash", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment->getId(), $jsonData['commentId']);
        self::assertSame('test comment', $jsonData['body']);
        self::assertSame('trashed', $jsonData['visibility']);
    }

    public function testApiCannotRestoreCommentAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post);

        $postCommentManager = $this->getService(PostCommentManager::class);
        $postCommentManager->trash($this->getUserByUsername('user'), $comment);

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/restore");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRestoreCommentWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $postCommentManager = $this->getService(PostCommentManager::class);
        $postCommentManager->trash($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/restore", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotRestoreComment(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $postCommentManager = $this->getService(PostCommentManager::class);
        $postCommentManager->trash($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/restore", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRestoreComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineManager = $this->getService(MagazineManager::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineManager->addModerator($moderator);

        $postCommentManager = $this->getService(PostCommentManager::class);
        $postCommentManager->trash($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/restore", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment->getId(), $jsonData['commentId']);
        self::assertSame('test comment', $jsonData['body']);
        self::assertSame('visible', $jsonData['visibility']);
    }
}
