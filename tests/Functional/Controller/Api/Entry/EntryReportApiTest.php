<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Entry;

use App\Entity\Report;
use App\Repository\MagazineRepository;
use App\Tests\WebTestCase;

class EntryReportApiTest extends WebTestCase
{
    public function testApiCannotReportEntryAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for report', magazine: $magazine);

        $reportRequest = [
            'reason' => 'Test reporting',
        ];

        $client->jsonRequest('POST', "/api/entry/{$entry->getId()}/report", $reportRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotReportEntryWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for report', user: $user, magazine: $magazine);

        $reportRequest = [
            'reason' => 'Test reporting',
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/entry/{$entry->getId()}/report", $reportRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanReportEntry(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $otherUser = $this->getUserByUsername('somebody');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $entry = $this->getEntryByTitle('test article', body: 'test for report', user: $otherUser, magazine: $magazine);

        $reportRequest = [
            'reason' => 'Test reporting',
        ];

        $magazineRepository = $this->getService(MagazineRepository::class);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read entry:report');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/entry/{$entry->getId()}/report", $reportRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);

        $magazine = $magazineRepository->find($magazine->getId());
        $reports = $magazineRepository->findReports($magazine);
        self::assertSame(1, $reports->count());

        /** @var Report $report */
        $report = $reports->getCurrentPageResults()[0];

        self::assertEquals('Test reporting', $report->reason);
        self::assertSame($user->getId(), $report->reporting->getId());
        self::assertSame($otherUser->getId(), $report->reported->getId());
        self::assertSame($entry->getId(), $report->getSubject()->getId());
    }
}
