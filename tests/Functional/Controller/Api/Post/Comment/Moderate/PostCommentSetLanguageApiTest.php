<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment\Moderate;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class PostCommentSetLanguageApiTest extends WebTestCase
{
    public function testApiCannotSetCommentLanguageAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/de");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotSetCommentLanguageWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
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

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/de", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotSetCommentLanguage(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user2);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/de", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSetCommentLanguage(): void
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

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/de", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment->getId(), $jsonData['commentId']);
        self::assertSame('test comment', $jsonData['body']);
        self::assertSame('de', $jsonData['lang']);
    }
}
