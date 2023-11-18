<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Moderate;

use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class PostSetAdultApiTest extends WebTestCase
{
    public function testApiCannotSetPostAdultAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', magazine: $magazine);

        $client->jsonRequest('PUT', "/api/moderate/post/{$post->getId()}/adult/true");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiNonModeratorCannotSetPostAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotSetPostAdultWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme', $user);
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSetPostAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/adult/true",
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
        self::assertTrue($jsonData['isAdult']);
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
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotSetPostNotAdultAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', magazine: $magazine);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $post->isAdult = true;
        $entityManager->persist($post);
        $entityManager->flush();

        $client->request('PUT', "/api/moderate/post/{$post->getId()}/adult/false");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiNonModeratorCannotSetPostNotAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $post->isAdult = true;
        $entityManager->persist($post);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotSetPostNotAdultWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme', $user);
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $post->isAdult = true;
        $entityManager->persist($post);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSetPostNotAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test article', user: $user, magazine: $magazine);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $post->isAdult = true;
        $entityManager->persist($post);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/post/{$post->getId()}/adult/false",
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
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
