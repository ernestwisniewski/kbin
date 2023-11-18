<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Comment\Moderate;

use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Kbin\Magazine\Moderator\MagazineModeratorAdd;
use App\Repository\EntryCommentRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntryCommentSetAdultApiTest extends WebTestCase
{
    public function testApiCannotSetCommentAdultAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $client->jsonRequest('PUT', "/api/moderate/comment/{$comment->getId()}/adult/true");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotSetCommentAdultWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('user2');
        $entry = $this->getEntryByTitle('an entry', body: 'test', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotSetCommentAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanSetCommentAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('other');
        $entry = $this->getEntryByTitle('an entry', body: 'test', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/adult/true",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertTrue($jsonData['isAdult']);
    }

    public function testApiCannotUnsetCommentAdultAnonymous(): void
    {
        $client = self::createClient();
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        $client->jsonRequest('PUT', "/api/moderate/comment/{$comment->getId()}/adult/false");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotUnsetCommentAdultWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('user2');
        $entry = $this->getEntryByTitle('an entry', body: 'test', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonModCannotUnsetCommentAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $user2 = $this->getUserByUsername('user2');
        $entry = $this->getEntryByTitle('an entry', body: 'test');
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanUnsetCommentAdult(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $user2 = $this->getUserByUsername('other');
        $entry = $this->getEntryByTitle('an entry', body: 'test', magazine: $magazine);
        $comment = $this->createEntryComment('test comment', $entry, $user2);

        $magazineModeratorAdd = $this->getService(MagazineModeratorAdd::class);
        $moderator = new MagazineModeratorDto($magazine);
        $moderator->user = $user;
        $magazineModeratorAdd($moderator);

        $entityManager = $this->getService(EntityManagerInterface::class);
        $comment->isAdult = true;
        $entityManager->persist($comment);
        $entityManager->flush();

        $commentRepository = $this->getService(EntryCommentRepository::class);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read moderate:entry_comment:set_adult');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest(
            'PUT',
            "/api/moderate/comment/{$comment->getId()}/adult/false",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(200);
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::ENTRY_COMMENT_RESPONSE_KEYS, $jsonData);
        self::assertFalse($jsonData['isAdult']);

        $comment = $commentRepository->find($comment->getId());
        self::assertFalse($comment->isAdult);
    }
}
