<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment;

use App\Kbin\Favourite\FavouriteToggle;
use App\Kbin\Vote\VoteCreate;
use App\Tests\WebTestCase;

class EntryCommentVoteApiTest extends WebTestCase
{
    public function testApiCannotUpvoteCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/1");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpvoteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpvoteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(1, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(1, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }

    public function testApiCannotDownvoteCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/-1");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDownvoteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/-1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDownvoteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/-1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(1, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(-1, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }

    public function testApiCannotRemoveVoteCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(1, $comment, $this->getUserByUsername('user'), rateLimit: false);

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/0");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRemoveVoteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(1, $comment, $user, rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/0", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRemoveVoteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        $voteCreate = $this->getService(VoteCreate::class);
        $voteCreate(1, $comment, $user, rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/vote/0", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }

    public function testApiCannotFavouriteCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $client->request('PUT', "/api/comments/{$comment->getId()}/favourite");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotFavouriteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanFavouriteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(1, $jsonData['favourites']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertTrue($jsonData['isFavourited']);
    }

    public function testApiCannotUnfavouriteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        $favouriteToggle = $this->getService(FavouriteToggle::class);
        $favouriteToggle($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUnfavouriteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        $favouriteToggle = $this->getService(FavouriteToggle::class);
        $favouriteToggle($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }
}
