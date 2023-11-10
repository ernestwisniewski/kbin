<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry;

use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EntryCreateApiTest extends WebTestCase
{
    public function testApiCannotCreateArticleEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'Anonymous Thread',
            'body' => 'This is an article',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/article", parameters: $entryRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateArticleEntryWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'No Scope Thread',
            'body' => 'This is an article',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/article", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateArticleEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'Test Thread',
            'body' => 'This is an article',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/article", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertNotNull($jsonData['entryId']);
        self::assertEquals('Test Thread', $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals('This is an article', $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame(['test'], $jsonData['tags']);
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
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('Test-Thread', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotCreateLinkEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'Anonymous Thread',
            'url' => 'https://google.com',
            'body' => 'google',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/link", parameters: $entryRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateLinkEntryWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'No Scope Thread',
            'url' => 'https://google.com',
            'body' => 'google',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/link", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateLinkEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'Test Thread',
            'url' => 'https://google.com',
            'body' => 'This is a link',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/link", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertNotNull($jsonData['entryId']);
        self::assertEquals('Test Thread', $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertEquals('https://google.com', $jsonData['url']);
        self::assertEquals('This is a link', $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame(['test'], $jsonData['tags']);
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
        self::assertEquals('link', $jsonData['type']);
        self::assertEquals('Test-Thread', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotCreateImageEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'Anonymous Thread',
            'alt' => 'It\'s kibby!',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $client->request(
            'POST', "/api/magazine/{$magazine->getId()}/image",
            parameters: $entryRequest, files: ['uploadImage' => $image],
        );
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateImageEntryWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'No Scope Thread',
            'alt' => 'It\'s kibby!',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST', "/api/magazine/{$magazine->getId()}/image",
            parameters: $entryRequest, files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateImageEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'Test Thread',
            'alt' => 'It\'s kibby!',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST', "/api/magazine/{$magazine->getId()}/image",
            parameters: $entryRequest, files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );
        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertNotNull($jsonData['entryId']);
        self::assertEquals('Test Thread', $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertNull($jsonData['body']);
        self::assertIsArray($jsonData['image']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['image']);
        self::assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $jsonData['image']['filePath']);
        self::assertEquals('It\'s kibby!', $jsonData['image']['altText']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertIsArray($jsonData['tags']);
        self::assertSame(['test'], $jsonData['tags']);
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
        self::assertEquals('image', $jsonData['type']);
        self::assertEquals('Test-Thread', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCannotCreateEntryWithoutMagazine(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $invalidId = $magazine->getId() + 1;
        $entryRequest = [
            'title' => 'No Url/Body Thread',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/magazine/{$invalidId}/article", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(404);

        $client->jsonRequest('POST', "/api/magazine/{$invalidId}/link", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(404);

        $client->request('POST', "/api/magazine/{$invalidId}/image", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(404);
    }

    public function testApiCannotCreateEntryWithoutUrlBodyOrImage(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entryRequest = [
            'title' => 'No Url/Body Thread',
            'tags' => ['test'],
            'isOc' => false,
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/article", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);

        $client->jsonRequest('POST', "/api/magazine/{$magazine->getId()}/link", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);

        $client->request('POST', "/api/magazine/{$magazine->getId()}/image", parameters: $entryRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(400);
    }
}
