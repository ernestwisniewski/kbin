<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post;

use App\Tests\WebTestCase;

class PostDeleteApiTest extends WebTestCase
{
    public function testApiCannotDeletePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost(body: 'test for deletion', magazine: $magazine);

        $client->request('DELETE', "/api/post/{$post->getId()}");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeletePostWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost(body: 'test for deletion', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotDeleteOtherUsersPost(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost(body: 'test for deletion', user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeletePost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost(body: 'test for deletion', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }

    public function testApiCannotDeleteImagePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, magazine: $magazine);

        $client->request('DELETE', "/api/post/{$post->getId()}");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDeleteImagePostWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotDeleteOtherUsersImagePost(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDeleteImagePost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:delete');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }
}
