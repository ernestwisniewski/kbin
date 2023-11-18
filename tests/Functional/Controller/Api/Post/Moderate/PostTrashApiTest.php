<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Moderate;

use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Kbin\Post\PostTrash;
use App\Tests\WebTestCase;

class PostTrashApiTest extends WebTestCase
{
    public function testApiCannotTrashPostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', magazine: $magazine);

        $client->jsonRequest('PUT', "/api/moderate/post/{$post->getId()}/trash");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiNonModeratorCannotTrashPost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/trash",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotTrashPostWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme', $user);
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/trash",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanTrashPost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/trash",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertEquals($post->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($post->lang, $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertNull($jsonData['mentions']);
        self::assertSame(0, $jsonData['comments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('trashed', $jsonData['visibility']);
        self::assertEquals('test-post', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotRestorePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('test post', magazine: $magazine);

        $postTrash = $this->getService(PostTrash::class);
        $postTrash($user, $post);

        $client->jsonRequest('PUT', "/api/moderate/post/{$post->getId()}/restore");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiNonModeratorCannotRestorePost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        $postTrash = $this->getService(PostTrash::class);
        $postTrash($user, $post);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/restore",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRestorePostWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme', $user);
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        $postTrash = $this->getService(PostTrash::class);
        $postTrash($user, $post);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/restore",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRestorePost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test post', user: $user, magazine: $magazine);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        $postTrash = $this->getService(PostTrash::class);
        $postTrash($user, $post);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:trash');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/restore",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertEquals($post->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($post->lang, $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertNull($jsonData['mentions']);
        self::assertSame(0, $jsonData['comments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('visible', $jsonData['visibility']);
        self::assertEquals('test-post', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
