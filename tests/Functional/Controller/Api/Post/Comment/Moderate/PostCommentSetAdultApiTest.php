<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment\Moderate;

use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Repository\PostCommentRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class PostCommentSetAdultApiTest extends WebTestCase
{
    public function testApiCannotSetCommentAdultAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post);

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/adult/true");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotSetCommentAdultWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post-comment/{$comment->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotSetCommentAdult(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post-comment/{$comment->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSetCommentAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post-comment/{$comment->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertTrue($jsonData['isAdult']);
    }

    public function testApiCannotUnsetCommentAdultAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        $client->jsonRequest('PUT', "/api/moderate/post-comment/{$comment->getId()}/adult/false");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUnsetCommentAdultWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post-comment/{$comment->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotUnsetCommentAdult(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $post = $this->createPost('a post', $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post-comment/{$comment->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUnsetCommentAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user2 = $this->getUserByUsername('other');
        $post = $this->createPost('a post', magazine: $magazine);
        $comment = $this->createPostComment('test comment', $post, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        $commentRepository = $this->getService(PostCommentRepository::class);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post-comment/{$comment->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertFalse($jsonData['isAdult']);

        $comment = $commentRepository->find($comment->getId());
        self::assertFalse($comment->isAdult);
    }
}
