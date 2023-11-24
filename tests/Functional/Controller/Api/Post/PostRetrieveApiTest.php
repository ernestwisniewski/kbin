<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post;

use App\Kbin\Favourite\FavouriteToggle;
use App\Kbin\Post\PostPin;
use App\Kbin\Vote\VoteCreate;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class PostRetrieveApiTest extends WebTestCase
{
    public function testApiCannotGetSubscribedPostsAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/posts/subscribed');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetSubscribedPostsWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'write');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetSubscribedPosts(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $this->createPost('a post');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag', $user);
        $post = $this->createPost('another post', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts/subscribed', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('another post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(0, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('another-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }

    public function testApiCannotGetModeratedPostsAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/posts/moderated');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetModeratedPostsWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts/moderated', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetModeratedPosts(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $this->createPost('a post');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag', $user);
        $post = $this->createPost('another post', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:post');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts/moderated', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertEquals('another post', $jsonData['items'][0]['body']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(0, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('another-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }

    public function testApiCannotGetFavouritedPostsAnonymous(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/posts/favourited');
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotGetFavouritedPostsWithoutScope(): void
    {
        $client = self::createClient();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts/favourited', server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetFavouritedPosts(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine);

        $favouriteToggle = $this->getService(FavouriteToggle::class);
        $favouriteToggle($user, $post);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts/favourited', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(0, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(1, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertTrue($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);
    }

    public function testApiCanGetPostsAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->createPost('another post', magazine: $magazine);
        // Check that pinned posts don't get pinned to the top of the instance, just the magazine
        $postPin = $this->getService(PostPin::class);
        $postPin($second);

        $client->request('GET', '/api/posts');
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(1, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertSame(0, $jsonData['items'][1]['comments']);
    }

    public function testApiCanGetPosts(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(1, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertSame(0, $jsonData['items'][1]['comments']);
    }

    public function testApiCanGetPostsWithLanguageAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->createPost('another post', magazine: $magazine, lang: 'de');
        $this->createPost('a dutch post', magazine: $magazine, lang: 'nl');
        // Check that pinned posts don't get pinned to the top of the instance, just the magazine
        $postPin = $this->getService(PostPin::class);
        $postPin($second);

        $client->request('GET', '/api/posts?lang[]=en&lang[]=de');
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(1, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('de', $jsonData['items'][1]['lang']);
        self::assertSame(0, $jsonData['items'][1]['comments']);
    }

    public function testApiCanGetPostsWithLanguage(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine, lang: 'de');
        $this->createPost('a dutch post', magazine: $magazine, lang: 'nl');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts?lang[]=en&lang[]=de', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(1, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('de', $jsonData['items'][1]['lang']);
        self::assertSame(0, $jsonData['items'][1]['comments']);
    }

    public function testApiCannotGetPostsByPreferredLangAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $second = $this->createPost('another post', magazine: $magazine);
        // Check that pinned posts don't get pinned to the top of the instance, just the magazine
        $postPin = $this->getService(PostPin::class);
        $postPin($second);

        $client->request('GET', '/api/posts?usePreferredLangs=true');
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanGetPostsByPreferredLang(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine);
        $this->createPost('German post', lang: 'de');

        $user = $this->getUserByUsername('user');
        $user->preferredLanguages = ['en'];
        $entityManager = $this->getService(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts?usePreferredLangs=true', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertNull($jsonData['items'][0]['mentions']);
        self::assertSame(1, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        // No scope for seeing votes granted
        self::assertNull($jsonData['items'][0]['isFavourited']);
        self::assertNull($jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertEquals('en', $jsonData['items'][1]['lang']);
        self::assertSame(0, $jsonData['items'][1]['comments']);
    }

    public function testApiCanGetPostsNewest(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');

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

        $client->request('GET', '/api/posts?sort=newest', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['postId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['postId']);
    }

    public function testApiCanGetPostsOldest(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');

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

        $client->request('GET', '/api/posts?sort=oldest', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['postId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['postId']);
    }

    public function testApiCanGetPostsCommented(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $this->createPostComment('comment 1', $first);
        $this->createPostComment('comment 2', $first);
        $second = $this->createPost('second');
        $this->createPostComment('comment 1', $second);
        $third = $this->createPost('third');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts?sort=commented', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['postId']);
        self::assertSame(2, $jsonData['items'][0]['comments']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);
        self::assertSame(1, $jsonData['items'][1]['comments']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['postId']);
        self::assertSame(0, $jsonData['items'][2]['comments']);
    }

    public function testApiCanGetPostsActive(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');

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

        $client->request('GET', '/api/posts?sort=active', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($third->getId(), $jsonData['items'][0]['postId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($first->getId(), $jsonData['items'][2]['postId']);
    }

    public function testApiCanGetPostsTop(): void
    {
        $client = self::createClient();
        $first = $this->createPost('first');
        $second = $this->createPost('second');
        $third = $this->createPost('third');

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(1, $first, $this->getUserByUsername('voter1'), rateLimit: false);
        $voteCreate(1, $first, $this->getUserByUsername('voter2'), rateLimit: false);
        $voteCreate(1, $second, $this->getUserByUsername('voter1'), rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts?sort=top', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($first->getId(), $jsonData['items'][0]['postId']);
        self::assertSame(2, $jsonData['items'][0]['uv']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertSame($second->getId(), $jsonData['items'][1]['postId']);
        self::assertSame(1, $jsonData['items'][1]['uv']);

        self::assertIsArray($jsonData['items'][2]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][2]);
        self::assertSame($third->getId(), $jsonData['items'][2]['postId']);
        self::assertSame(0, $jsonData['items'][2]['uv']);
    }

    public function testApiCanGetPostsWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $this->createPostComment('up the ranking', $post);
        $magazine = $this->getMagazineByNameNoRSAKey('somemag');
        $this->createPost('another post', magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', '/api/posts', server: ['HTTP_AUTHORIZATION' => $token]);
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
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertSame($post->getId(), $jsonData['items'][0]['postId']);
        self::assertEquals('a post', $jsonData['items'][0]['body']);
        self::assertIsArray($jsonData['items'][0]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['magazine']);
        self::assertIsArray($jsonData['items'][0]['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['user']);
        self::assertNull($jsonData['items'][0]['image']);
        self::assertEquals('en', $jsonData['items'][0]['lang']);
        self::assertNull($jsonData['items'][0]['tags']);
        self::assertSame(1, $jsonData['items'][0]['comments']);
        self::assertSame(0, $jsonData['items'][0]['uv']);
        self::assertSame(0, $jsonData['items'][0]['dv']);
        self::assertSame(0, $jsonData['items'][0]['favourites']);
        self::assertFalse($jsonData['items'][0]['isFavourited']);
        self::assertSame(0, $jsonData['items'][0]['userVote']);
        self::assertFalse($jsonData['items'][0]['isAdult']);
        self::assertFalse($jsonData['items'][0]['isPinned']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['createdAt'],
            'createdAt date format invalid'
        );
        self::assertNull($jsonData['items'][0]['editedAt']);
        self::assertStringMatchesFormat(
            '%d-%d-%dT%d:%d:%d%i:00',
            $jsonData['items'][0]['lastActive'],
            'lastActive date format invalid'
        );
        self::assertEquals('a-post', $jsonData['items'][0]['slug']);
        self::assertNull($jsonData['items'][0]['apId']);

        self::assertIsArray($jsonData['items'][1]);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData['items'][1]);
        self::assertEquals('another post', $jsonData['items'][1]['body']);
        self::assertIsArray($jsonData['items'][1]['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['items'][1]['magazine']);
        self::assertSame($magazine->getId(), $jsonData['items'][1]['magazine']['magazineId']);
        self::assertSame(0, $jsonData['items'][1]['comments']);
    }

    public function testApiCanGetPostByIdAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');

        $client->request('GET', "/api/post/{$post->getId()}");
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertEquals('a post', $jsonData['body']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertNull($jsonData['mentions']);
        self::assertSame(0, $jsonData['comments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
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
        self::assertEquals('a-post', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCanGetPostById(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertEquals('a post', $jsonData['body']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
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
        self::assertEquals('a-post', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }

    public function testApiCanGetPostByIdWithUserVoteStatus(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('GET', "/api/post/{$post->getId()}", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_RESPONSE_KEYS, $jsonData);
        self::assertSame($post->getId(), $jsonData['postId']);
        self::assertEquals('a post', $jsonData['body']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertNull($jsonData['image']);
        self::assertEquals('en', $jsonData['lang']);
        self::assertNull($jsonData['tags']);
        self::assertSame(0, $jsonData['comments']);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertFalse($jsonData['isFavourited']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isAdult']);
        self::assertFalse($jsonData['isPinned']);
        // This API creates a view when used
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
        self::assertEquals('a-post', $jsonData['slug']);
        self::assertNull($jsonData['apId']);
    }
}
