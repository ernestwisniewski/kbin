<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentUpdateApiTest extends WebTestCase
{
    public function testApiCannotUpdateCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        $client->jsonRequest('PUT', "/api/comments/{$comment->getId()}", $update);

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUpdateCommentWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/comments/{$comment->getId()}", $update, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotUpdateOtherUsersComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('other');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/comments/{$comment->getId()}", $update, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUpdateComment(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user);
        $parent = $comment;
        for ($i = 0; $i < 5; ++$i) {
            $parent = $this->createEntryComment('test reply', $entry, $user, $parent);
        }

        $update = [
            'body' => 'updated body',
            'lang' => 'de',
            'isAdult' => true,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:edit');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('PUT', "/api/comments/{$comment->getId()}?d=2", $update, server: ['HTTP_AUTHORIZATION' => $token]);

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment->getId(), $jsonData['commentId']);
        self::assertSame($update['body'], $jsonData['body']);
        self::assertSame($update['lang'], $jsonData['lang']);
        self::assertSame($update['isAdult'], $jsonData['isAdult']);
        self::assertSame(5, $jsonData['childCount']);

        $depth = 0;
        $current = $jsonData;
        while (count($current['children']) > 0) {
            self::assertIsArray($current['children'][0]);
            self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $current['children'][0]);
            ++$depth;
            $current = $current['children'][0];
        }

        self::assertSame(2, $depth);
    }
}
