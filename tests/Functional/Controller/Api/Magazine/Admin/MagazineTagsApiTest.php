<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Admin;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class MagazineTagsApiTest extends WebTestCase
{
    public function testApiCannotAddTagsToMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/tag/test");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRemoveTagsFromMagazineAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $magazine->tags = ['test'];
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($magazine);
        $entityManager->flush();

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/tag/test");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotAddTagsToMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/tag/test", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRemoveTagsFromMagazineWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $magazine->tags = ['test'];
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($magazine);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/tag/test", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotAddTagsMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineManager = $this->getService(MagazineManager::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineManager->addModerator($dto);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:tags');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/tag/test", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiModCannotRemoveTagsMagazine(): void
    {
        $client = self::createClient();
        $moderator = $this->getUserByUsername('JohnDoe');
        $client->loginUser($moderator);
        $owner = $this->getUserByUsername('JaneDoe');
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test', $owner);
        $magazineManager = $this->getService(MagazineManager::class);
        $dto = new ModeratorDto($magazine);
        $dto->user = $moderator;
        $magazineManager->addModerator($dto);

        $magazine->tags = ['test'];
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($magazine);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:tags');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/tag/test", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiOwnerCanAddTagsMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:tags');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/tag/test", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['tags']);
        self::assertCount(1, $jsonData['tags']);
        self::assertEquals('test', $jsonData['tags'][0]);
    }

    public function testApiOwnerCannotAddWeirdTagsMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:tags');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('POST', "/api/moderate/magazine/{$magazine->getId()}/tag/test%20Weird", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testApiOwnerCanRemoveTagsMagazine(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();

        $magazine = $this->getMagazineByName('test');
        $magazine->tags = ['test'];
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($magazine);
        $entityManager->flush();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine_admin:tags');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/magazine/{$magazine->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertIsArray($jsonData['tags']);
        self::assertCount(1, $jsonData['tags']);
        self::assertEquals('test', $jsonData['tags'][0]);

        $client->request('DELETE', "/api/moderate/magazine/{$magazine->getId()}/tag/test", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_RESPONSE_KEYS, $jsonData);
        self::assertNull($jsonData['tags']);
    }
}
