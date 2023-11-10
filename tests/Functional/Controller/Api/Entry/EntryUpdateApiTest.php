<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry;

use App\Tests\WebTestCase;

class EntryUpdateApiTest extends WebTestCase
{
    public function testApiCannotUpdateArticleEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for update', magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateArticleEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for update', user: $user, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateOtherUsersArticleEntry(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for update', user: $otherUser, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateArticleEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for update', user: $user, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($updateRequest['title'], $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($updateRequest['body'], $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($updateRequest['lang'], $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame($updateRequest['tags'], $jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertTrue($jsonData['isOc']);
        self::assertTrue($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['editedAt'], 'editedAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('Updated-title', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotUpdateLinkEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateLinkEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', user: $user, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateOtherUsersLinkEntry(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', user: $otherUser, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateLinkEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', user: $user, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($updateRequest['title'], $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertEquals('https://google.com', $jsonData['url']);
        self::assertEquals($updateRequest['body'], $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals($updateRequest['lang'], $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame($updateRequest['tags'], $jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertTrue($jsonData['isOc']);
        self::assertTrue($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['editedAt'], 'editedAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('link', $jsonData['type']);
        self::assertEquals('Updated-title', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotUpdateImageEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateImageEntryWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, user: $user, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateOtherUsersImageEntry(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, user: $otherUser, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateImageEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, user: $user, magazine: $magazine);

        $updateRequest = [
            'title' => 'Updated title',
            'tags' => [
                'edit',
            ],
            'isOc' => true,
            'body' => 'Updated body',
            'lang' => 'nl',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/entry/{$entry->getId()}", $updateRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals($updateRequest['title'], $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals($updateRequest['body'], $jsonData['body']);
        self::assertIsArray($jsonData['image']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['image']);
        self::assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $jsonData['image']['filePath']);
        self::assertEquals($updateRequest['lang'], $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame($updateRequest['tags'], $jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertTrue($jsonData['isOc']);
        self::assertTrue($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['editedAt'], 'editedAt date format invalid');
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('image', $jsonData['type']);
        self::assertEquals('Updated-title', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
