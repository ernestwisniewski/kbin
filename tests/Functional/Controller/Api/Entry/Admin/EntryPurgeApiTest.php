<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry\Admin;

use App\Tests\WebTestCase;

class EntryPurgeApiTest extends WebTestCase
{
    public function testApiCannotPurgeArticleEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for deletion', magazine: $magazine);

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotPurgeArticleEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user', isAdmin: true);
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for deletion', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonAdminCannotPurgeArticleEntry(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for deletion', user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:entry:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanPurgeArticleEntry(): void
    {
        $client = self::createClient();
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for deletion', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($admin);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:entry:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }

    public function testApiCannotPurgeLinkEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', magazine: $magazine);

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotPurgeLinkEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user', isAdmin: true);
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonAdminCannotPurgeLinkEntry(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:entry:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanPurgeLinkEntry(): void
    {
        $client = self::createClient();
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test link', url: 'https://google.com', user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($admin);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:entry:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }

    public function testApiCannotPurgeImageEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, magazine: $magazine);

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge");
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotPurgeImageEntryWithoutScope(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $user = $this->getUserByUsername('user', isAdmin: true);

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiNonAdminCannotPurgeImageEntry(): void
    {
        $client = self::createClient();
        $otherUser = $this->getUserByUsername('somebody');
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, user: $otherUser, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:entry:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanPurgeImageEntry(): void
    {
        $client = self::createClient();
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');

        $imageDto = $this->getKibbyImageDto();
        $entry = $this->getEntryByTitle('test image', image: $imageDto, user: $user, magazine: $magazine);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($admin);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read admin:entry:purge');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request('DELETE', "/api/admin/entry/{$entry->getId()}/purge", server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);
    }
}
