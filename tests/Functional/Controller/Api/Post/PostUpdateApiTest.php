<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post;

use App\Tests\WebTestCase;

class PostUpdateApiTest extends WebTestCase
{
    public function testApiCannotUpdatePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdatePostWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateOtherUsersPost(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $otherUser, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdatePost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated #body @user',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertEquals($updateRequest['body'], $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($updateRequest['lang'], $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame(['body'], $jsonData['tags']);
        self::assertIsArray($jsonData['mentions']);
        self::assertSame(['@user'], $jsonData['mentions']);
        self::assertSame(0, $jsonData['comments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertTrue($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['editedAt'], 'editedAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('Updated-body-at-user', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotUpdateImagePostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateImagePostWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $user, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateOtherUsersImagePost(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $otherUser, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateImagePost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $post = $this->createPost('test image', imageDto: $imageDto, user: $user, magazine: $magazine);

        $updateRequest = [
            'body' => 'Updated #body @user',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/post/{$post->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertEquals($updateRequest['body'], $jsonData['body']);
        self::assertIsArray($jsonData['image']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['image']);
        self::assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $jsonData['image']['filePath']);
        self::assertEquals($updateRequest['lang'], $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame(['body'], $jsonData['tags']);
        self::assertIsArray($jsonData['mentions']);
        self::assertSame(['@user'], $jsonData['mentions']);
        self::assertSame(0, $jsonData['comments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertTrue($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['editedAt'], 'editedAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('Updated-body-at-user', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
