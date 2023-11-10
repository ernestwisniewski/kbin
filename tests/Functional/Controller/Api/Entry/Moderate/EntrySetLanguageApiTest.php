<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Moderate;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class EntrySetLanguageApiTest extends WebTestCase
{
    public function testApiCannotSetEntryLanguageAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for favourite', magazine: $magazine);

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/de");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiNonModeratorCannotSetEntryLanguage(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for favourite', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/de", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotSetEntryLanguageWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme', $user);
        $entry = $this->getEntryByTitle('test article', body: 'test for favourite', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/de", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotSetEntryLanguageInvalid(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for favourite', user: $user, magazine: $magazine);

        $magazineManager = $this->getService(MagazineManager::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineManager->addModerator($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/fake", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/ac", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/aaa", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/a", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);
    }

    public function testApiCanSetEntryLanguage(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for favourite', user: $user, magazine: $magazine);

        $magazineManager = $this->getService(MagazineManager::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineManager->addModerator($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/de", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($entry->title, $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($entry->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('de', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('visible', $jsonData['visibility']);
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCanSetEntryLanguage3Letter(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for favourite', user: $user, magazine: $magazine);

        $magazineManager = $this->getService(MagazineManager::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineManager->addModerator($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/moderate/entry/{$entry->getId()}/elx", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($entry->title, $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($entry->body, $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('elx', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('visible', $jsonData['visibility']);
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('test-article', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
