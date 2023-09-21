<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Admin;

use App\Tests\WebTestCase;

class PostPurgeApiTest extends WebTestCase
{
    public function testApiCannotPurgeArticlePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', magazine: $magazine);

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotPurgeArticlePostWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user', isAdmin: true);
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonAdminCannotPurgeArticlePost(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:post:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanPurgeArticlePost(): void
    {
        $client = self::createClient();
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($admin);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:post:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }

    public function testApiCannotPurgeImagePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, magazine: $magazine);

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotPurgeImagePostWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user', isAdmin: true);

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonAdminCannotPurgeImagePost(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:post:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanPurgeImagePost(): void
    {
        $client = self::createClient();
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($admin);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:post:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/post/{$post->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }
}
