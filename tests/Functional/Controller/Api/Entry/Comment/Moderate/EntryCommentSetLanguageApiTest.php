<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment\Moderate;

use App\DTO\ModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Tests\WebTestCase;

class EntryCommentSetLanguageApiTest extends WebTestCase
{
    public function testApiCannotSetCommentLanguageAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $client->jsonRequest('PUT', "/api/moderate/comment/{$comment->getId()}/de");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotSetCommentLanguageWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('user2');
        $entry = $this->getEntryByTitle('an entry', body: 'test', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/de",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotSetCommentLanguage(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry_comment:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/de",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSetCommentLanguage(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('other');
        $entry = $this->getEntryByTitle('an entry', body: 'test', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new ModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry_comment:language');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/de",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertSame($comment->getId(), $jsonData['commentId']);
        self::assertSame('test comment', $jsonData['body']);
        self::assertSame('de', $jsonData['lang']);
    }
}
