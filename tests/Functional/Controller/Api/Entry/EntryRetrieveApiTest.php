<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry;

use App\Service\EntryManager;
use App\Service\FavouriteManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntryRetrieveApiTest extends WebTestCase
{
    public function testApiCannotGetSubscribedEntriesAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/entries/subscribed');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetSubscribedEntriesWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'write');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetSubscribedEntries(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag', $user);
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('another entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertEquals('https://google.com', $jsonData['items'][0]['url']);
        self::assertNull($jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(0, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('link', $jsonData['items'][0]['type']);
        self::assertEquals('another-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }

    public function testApiCannotGetModeratedEntriesAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/entries/moderated');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetModeratedEntriesWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries/moderated', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetModeratedEntries(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag', $user);
        $entry = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries/moderated', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('another entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertEquals('https://google.com', $jsonData['items'][0]['url']);
        self::assertNull($jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(0, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('link', $jsonData['items'][0]['type']);
        self::assertEquals('another-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }

    public function testApiCannotGetFavouritedEntriesAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/entries/favourited');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetFavouritedEntriesWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries/favourited', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetFavouritedEntries(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle($user, $entry);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries/favourited', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(0, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(1, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertTrue($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }

    public function testApiCanGetEntriesAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        // Check that pinned entries don't get pinned to the top of the instance, just the magazine
        $entryManager = $this->getService(EntryManager::class);
        $entryManager->pin($second);

        $client->request('GET', '/api/entries');
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(1, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another entry', $jsonData['items'][1]['title']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('link', $jsonData['items'][1]['type']);
        self::assertSame(0, $jsonData['items'][1]['numComments']);
    }

    public function testApiCanGetEntries(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(1, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another entry', $jsonData['items'][1]['title']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('link', $jsonData['items'][1]['type']);
        self::assertSame(0, $jsonData['items'][1]['numComments']);
    }

    public function testApiCanGetEntriesWithLanguageAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine, lang: 'de');
        $this->getEntryByTitle('a dutch entry', body: 'some body', magazine: $magazine, lang: 'nl');
        // Check that pinned entries don't get pinned to the top of the instance, just the magazine
        $entryManager = $this->getService(EntryManager::class);
        $entryManager->pin($second);

        $client->request('GET', '/api/entries?lang[]=en&lang[]=de');
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(1, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another entry', $jsonData['items'][1]['title']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('link', $jsonData['items'][1]['type']);
        self::assertEquals('de', $jsonData['items'][1]['lang']);
        self::assertSame(0, $jsonData['items'][1]['numComments']);
    }

    public function testApiCanGetEntriesWithLanguage(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine, lang: 'de');
        $this->getEntryByTitle('a dutch entry', body: 'some body', magazine: $magazine, lang: 'nl');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?lang[]=en&lang[]=de', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(1, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another entry', $jsonData['items'][1]['title']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('link', $jsonData['items'][1]['type']);
        self::assertEquals('de', $jsonData['items'][1]['lang']);
        self::assertSame(0, $jsonData['items'][1]['numComments']);
    }

    public function testApiCannotGetEntriesByPreferredLangAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        // Check that pinned entries don't get pinned to the top of the instance, just the magazine
        $entryManager = $this->getService(EntryManager::class);
        $entryManager->pin($second);

        $client->request('GET', '/api/entries?usePreferredLangs=true');
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetEntriesByPreferredLang(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);
        $this->getEntryByTitle('German entry', body: 'Some body', lang: 'de');

        $user = $this->getUserByUsername('user');
        $user->preferredLanguages = ['en'];
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?usePreferredLangs=true', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertIsArray($jsonData['items'][0]['badges']);
        self::assertEmpty($jsonData['items'][0]['badges']);
        self::assertSame(1, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another entry', $jsonData['items'][1]['title']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('link', $jsonData['items'][1]['type']);
        self::assertEquals('en', $jsonData['items'][1]['lang']);
        self::assertSame(0, $jsonData['items'][1]['numComments']);
    }

    public function testApiCanGetEntriesNewest(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');

        $first->createdAt = new \DateTimeImmutable('-1 hour');
        $second->createdAt = new \DateTimeImmutable('-1 second');
        $third->createdAt = new \DateTimeImmutable();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($first);
        $entityManager->persist($second);
        $entityManager->persist($third);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?sort=newest', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['entryId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['entryId']);
    }

    public function testApiCanGetEntriesOldest(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');

        $first->createdAt = new \DateTimeImmutable('-1 hour');
        $second->createdAt = new \DateTimeImmutable('-1 second');
        $third->createdAt = new \DateTimeImmutable();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($first);
        $entityManager->persist($second);
        $entityManager->persist($third);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?sort=oldest', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['entryId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['entryId']);
    }

    public function testApiCanGetEntriesCommented(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $this->createEntryComment('comment 1', $first);
        $this->createEntryComment('comment 2', $first);
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $this->createEntryComment('comment 1', $second);
        $third = $this->getEntryByTitle('third', url: 'https://google.com');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?sort=commented', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['entryId']);
        self::assertSame(2, $jsonData['items'][0]['numComments']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);
        self::assertSame(1, $jsonData['items'][1]['numComments']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['entryId']);
        self::assertSame(0, $jsonData['items'][2]['numComments']);
    }

    public function testApiCanGetEntriesActive(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');

        $first->lastActive = new \DateTime('-1 hour');
        $second->lastActive = new \DateTime('-1 second');
        $third->lastActive = new \DateTime();

        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($first);
        $entityManager->persist($second);
        $entityManager->persist($third);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?sort=active', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['entryId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['entryId']);
    }

    public function testApiCanGetEntriesTop(): void
    {
        $client = self::createClient();
        $first = $this->getEntryByTitle('first', body: 'test');
        $second = $this->getEntryByTitle('second', url: 'https://google.com');
        $third = $this->getEntryByTitle('third', url: 'https://google.com');

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter1'), rateLimit: false);
        $voteManager->vote(1, $first, $this->getUserByUsername('voter2'), rateLimit: false);
        $voteManager->vote(1, $second, $this->getUserByUsername('voter1'), rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries?sort=top', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(3, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(3, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['entryId']);
        self::assertSame(2, $jsonData['items'][0]['uv']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['entryId']);
        self::assertSame(1, $jsonData['items'][1]['uv']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['entryId']);
        self::assertSame(0, $jsonData['items'][2]['uv']);
    }

    public function testApiCanGetEntriesWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $this->createEntryComment('up the ranking', $entry);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->getEntryByTitle('another entry', url: 'https://google.com', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/entries', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);

        self::assertIsArray($jsonData['items']);
        self::assertCount(2, $jsonData['items']);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(2, $jsonData['pagination']['count']);

        self::assertIsArray($jsonData['items'][0]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($entry->getId(), $jsonData['items'][0]['entryId']);
        self::assertEquals('an entry', $jsonData['items'][0]['title']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertIsArray($jsonData['items'][0]['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['items'][0]['domain']);
        self::assertNull($jsonData['items'][0]['url']);
        self::assertEquals('test', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertSame(1, $jsonData['items'][0]['numComments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertFalse($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isOc']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['items'][0]['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['items'][0]['type']);
        self::assertEquals('an-entry', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another entry', $jsonData['items'][1]['title']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('link', $jsonData['items'][1]['type']);
        self::assertSame(0, $jsonData['items'][1]['numComments']);
    }

    public function testApiCanGetEntryByIdAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $client->request('GET', "/api/entry/{$entry->getId()}");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals('an entry', $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals('test', $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertIsArray($jsonData['badges']);
        self::assertEmpty($jsonData['badges']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertNull($jsonData['isFavourited']);
        self::assertNull($jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('an-entry', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCanGetEntryById(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals('an entry', $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals('test', $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
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
        self::assertEquals('an-entry', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCanGetEntryByIdWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/entry/{$entry->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertEquals('an entry', $jsonData['title']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertIsArray($jsonData['domain']);
        self::assertArrayKeysMatch(self::DOMAIN_RESPONSE_KEYS, $jsonData['domain']);
        self::assertNull($jsonData['url']);
        self::assertEquals('test', $jsonData['body']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertSame(0, $jsonData['numComments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertFalse($jsonData['isFavourited']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isOc']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['createdAt'], 'createdAt date format invalid');
        self::assertNull($jsonData['editedAt']);
        self::assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i:00', $jsonData['lastActive'], 'lastActive date format invalid');
        self::assertEquals('article', $jsonData['type']);
        self::assertEquals('an-entry', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
