<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post\Comment;

use App\Service\FavouriteManager;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class PostCommentVoteApiTest extends WebTestCase
{
    public function testApiCannotUpvoteCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/1");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpvoteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpvoteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(1, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(1, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }

    public function testApiCannotDownvoteCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/-1");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotDownvoteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/-1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanDownvoteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/-1", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(1, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(-1, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }

    public function testApiCannotRemoveVoteCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $comment, $this->getUserByUsername('user'), rateLimit: false);

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/0");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRemoveVoteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $comment, $user, rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/0", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRemoveVoteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $voteManager = $this->getService(VoteManager::class);
        $voteManager->vote(1, $comment, $user, rateLimit: false);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/vote/0", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }

    public function testApiCannotFavouriteCommentAnonymous(): void
    {
        $client = self::createClient();
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post);

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/favourite");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotFavouriteCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanFavouriteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
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
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUnfavouriteComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $post = $this->createPost('a post');
        $comment = $this->createPostComment('test comment', $post, $user);

        $favouriteManager = $this->getService(FavouriteManager::class);
        $favouriteManager->toggle($user, $comment);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post_comment:vote');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('PUT', "/api/post-comments/{$comment->getId()}/favourite", server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::POST_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame(0, $jsonData['uv']);
        self::assertSame(0, $jsonData['dv']);
        self::assertSame(0, $jsonData['favourites']);
        self::assertSame(0, $jsonData['userVote']);
        self::assertFalse($jsonData['isFavourited']);
    }
}
