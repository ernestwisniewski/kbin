<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment;

use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EntryCommentCreateApiTest extends WebTestCase
{
    public function testApiCannotCreateCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
        ];

        $client->jsonRequest(
            'POST', "/api/entry/{$entry->getId()}/comments",
            parameters: $comment
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateCommentWithoutScope(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'POST', "/api/entry/{$entry->getId()}/comments",
            parameters: $comment, server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateComment(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('user');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'POST', "/api/entry/{$entry->getId()}/comments",
            parameters: $comment, server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment['body'], $jsonData['body']);
        self::assertSame($comment['lang'], $jsonData['lang']);
        self::assertSame($comment['isAdult'], $jsonData['isAdult']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertNull($jsonData['rootId']);
        self::assertNull($jsonData['parentId']);
    }

    public function testApiCannotCreateCommentReplyAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $entryComment = $this->createEntryComment('a comment', $entry);

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
        ];

        $client->jsonRequest(
            'POST', "/api/entry/{$entry->getId()}/comments/{$entryComment->getId()}/reply",
            parameters: $comment
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateCommentReplyWithoutScope(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $entryComment = $this->createEntryComment('a comment', $entry);

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'POST', "/api/entry/{$entry->getId()}/comments/{$entryComment->getId()}/reply",
            parameters: $comment, server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateCommentReply(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $entryComment = $this->createEntryComment('a comment', $entry);

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
        ];

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('user');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'POST', "/api/entry/{$entry->getId()}/comments/{$entryComment->getId()}/reply",
            parameters: $comment, server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment['body'], $jsonData['body']);
        self::assertSame($comment['lang'], $jsonData['lang']);
        self::assertSame($comment['isAdult'], $jsonData['isAdult']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertSame($entryComment->getId(), $jsonData['rootId']);
        self::assertSame($entryComment->getId(), $jsonData['parentId']);
    }

    public function testApiCannotCreateImageCommentAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
            'alt' => 'It\'s Kibby!',
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $client->request(
            'POST', "/api/entry/{$entry->getId()}/comments/image",
            parameters: $comment, files: ['uploadImage' => $image]
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateImageCommentWithoutScope(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
            'alt' => 'It\'s Kibby!',
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        self::createOAuth2AuthCodeClient();
        $client->loginUser($this->getUserByUsername('user'));

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST', "/api/entry/{$entry->getId()}/comments/image",
            parameters: $comment, files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateImageComment(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
            'alt' => 'It\'s Kibby!',
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('user');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST', "/api/entry/{$entry->getId()}/comments/image",
            parameters: $comment, files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment['body'], $jsonData['body']);
        self::assertSame($comment['lang'], $jsonData['lang']);
        self::assertSame($comment['isAdult'], $jsonData['isAdult']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertNull($jsonData['rootId']);
        self::assertNull($jsonData['parentId']);
    }

    public function testApiCannotCreateImageCommentReplyAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $entryComment = $this->createEntryComment('a comment', $entry);

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
            'alt' => 'It\'s Kibby!',
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        $client->request(
            'POST', "/api/entry/{$entry->getId()}/comments/{$entryComment->getId()}/reply/image",
            parameters: $comment, files: ['uploadImage' => $image]
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotCreateImageCommentReplyWithoutScope(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $entryComment = $this->createEntryComment('a comment', $entry);

        $comment = [
            'body' => 'Test comment',
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
            'POST', "/api/entry/{$entry->getId()}/comments/{$entryComment->getId()}/reply/image",
            parameters: $comment, files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanCreateImageCommentReply(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $entryComment = $this->createEntryComment('a comment', $entry);

        $comment = [
            'body' => 'Test comment',
            'lang' => 'en',
            'isAdult' => false,
            'alt' => 'It\'s Kibby!',
        ];

        // Uploading a file appears to delete the file at the given path, so make a copy before upload
        copy($this->kibbyPath, $this->kibbyPath.'.tmp');
        $image = new UploadedFile($this->kibbyPath.'.tmp', 'kibby_emoji.png', 'image/png');

        self::createOAuth2AuthCodeClient();
        $user = $this->getUserByUsername('user');
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry_comment:create');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'POST', "/api/entry/{$entry->getId()}/comments/{$entryComment->getId()}/reply/image",
            parameters: $comment, files: ['uploadImage' => $image],
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(201);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment['body'], $jsonData['body']);
        self::assertSame($comment['lang'], $jsonData['lang']);
        self::assertSame($comment['isAdult'], $jsonData['isAdult']);
        self::assertSame($entry->getId(), $jsonData['entryId']);
        self::assertIsArray($jsonData['magazine']);
        self::assertArrayKeysMatch(self::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($entry->magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertIsArray($jsonData['user']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['user']);
        self::assertSame($user->getId(), $jsonData['user']['userId']);
        self::assertSame($entryComment->getId(), $jsonData['rootId']);
        self::assertSame($entryComment->getId(), $jsonData['parentId']);
        self::assertIsArray($jsonData['image']);
        self::assertArrayKeysMatch(self::IMAGE_KEYS, $jsonData['image']);
        self::assertEquals(self::KIBBY_PNG_URL_RESULT, $jsonData['image']['filePath']);
    }
}
