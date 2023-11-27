<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Magazine\Moderate;

use App\DTO\ReportDto;
use App\Kbin\Report\ReportCreate;
use App\Tests\Functional\Controller\Api\Magazine\MagazineRetrieveApiTest;
use App\Tests\WebTestCase;

class MagazineRetrieveReportsApiTest extends WebTestCase
{
    public const REPORT_RESPONSE_KEYS = [
        'reportId',
        'type',
        'magazine',
        'reason',
        'reported',
        'reporting',
        'subject',
        'status',
        'weight',
        'createdAt',
        'consideredAt',
        'consideredBy',
    ];

    public function testApiCannotRetrieveMagazineReportByIdAnonymous(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $magazine = $this->getMagazineByName('test');
        $reportedUser = $this->getUserByUsername('hapless_fool');
        $entry = $this->getEntryByTitle(
            'Report test',
            body: 'This is gonna be reported',
            magazine: $magazine,
            user: $reportedUser
        );

        $reportCreate = $this->getService(ReportCreate::class);
        $report = $reportCreate(ReportDto::create($entry, 'I don\'t like it'), $user);
        $client->request('GET', "/api/moderate/magazine/{$magazine->getId()}/reports/{$report->getId()}");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveMagazineReportByIdWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');
        $reportedUser = $this->getUserByUsername('hapless_fool');
        $entry = $this->getEntryByTitle(
            'Report test',
            body: 'This is gonna be reported',
            magazine: $magazine,
            user: $reportedUser
        );

        $reportCreate = $this->getService(ReportCreate::class);
        $report = $reportCreate(ReportDto::create($entry, 'I don\'t like it'), $user);

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/moderate/magazine/{$magazine->getId()}/reports/{$report->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveMagazineReportByIdIfNotMod(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test', $this->getUserByUsername('JaneDoe'));
        $reportedUser = $this->getUserByUsername('hapless_fool');
        $entry = $this->getEntryByTitle(
            'Report test',
            body: 'This is gonna be reported',
            magazine: $magazine,
            user: $reportedUser
        );

        $reportCreate = $this->getService(ReportCreate::class);
        $report = $reportCreate(ReportDto::create($entry, 'I don\'t like it'), $user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:reports:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/moderate/magazine/{$magazine->getId()}/reports/{$report->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveMagazineReportById(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');
        $reportedUser = $this->getUserByUsername('hapless_fool');
        $entry = $this->getEntryByTitle(
            'Report test',
            body: 'This is gonna be reported',
            magazine: $magazine,
            user: $reportedUser
        );

        $reportCreate = $this->getService(ReportCreate::class);
        $report = $reportCreate(ReportDto::create($entry, 'I don\'t like it'), $user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:reports:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/moderate/magazine/{$magazine->getId()}/reports/{$report->getId()}",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::REPORT_RESPONSE_KEYS, $jsonData);
        self::assertEquals($report->reason, $jsonData['reason']);
        self::assertEquals('entry_report', $jsonData['type']);
        self::assertArrayKeysMatch(MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS, $jsonData['magazine']);
        self::assertSame($magazine->getId(), $jsonData['magazine']['magazineId']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['reported']);
        self::assertSame($reportedUser->getId(), $jsonData['reported']['userId']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['reporting']);
        self::assertSame($user->getId(), $jsonData['reporting']['userId']);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['subject']);
        self::assertSame($entry->getId(), $jsonData['subject']['entryId']);
        self::assertEquals('pending', $jsonData['status']);
        self::assertSame(1, $jsonData['weight']);
        self::assertNull($jsonData['consideredAt']);
        self::assertNull($jsonData['consideredBy']);
        self::assertEquals(
            $report->createdAt->getTimestamp(),
            \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $jsonData['createdAt'])->getTimestamp()
        );
    }

    public function testApiCannotRetrieveMagazineReportsAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByName('test');
        $client->request('GET', "/api/moderate/magazine/{$magazine->getId()}/reports");

        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotRetrieveMagazineReportsWithoutScope(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');

        $codes = self::getAuthorizationCodeTokenResponse($client);
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/moderate/magazine/{$magazine->getId()}/reports",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCannotRetrieveMagazineReportsIfNotMod(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        self::createOAuth2AuthCodeClient();

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:reports:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $magazine = $this->getMagazineByName('test', $this->getUserByUsername('JaneDoe'));
        $client->request(
            'GET',
            "/api/moderate/magazine/{$magazine->getId()}/reports",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanRetrieveMagazineReports(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('JohnDoe');
        $client->loginUser($user);
        self::createOAuth2AuthCodeClient();
        $magazine = $this->getMagazineByName('test');

        $reportedUser = $this->getUserByUsername('hapless_fool');
        $entry = $this->getEntryByTitle(
            'Report test',
            body: 'This is gonna be reported',
            magazine: $magazine,
            user: $reportedUser
        );

        $reportCreate = $this->getService(ReportCreate::class);
        $report = $reportCreate(ReportDto::create($entry, 'I don\'t like it'), $user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read write moderate:magazine:reports:read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->request(
            'GET',
            "/api/moderate/magazine/{$magazine->getId()}/reports",
            server: ['HTTP_AUTHORIZATION' => $token]
        );

        self::assertResponseIsSuccessful();
        $jsonData = self::getJsonResponse($client);

        self::assertIsArray($jsonData);
        self::assertArrayKeysMatch(self::PAGINATED_KEYS, $jsonData);
        self::assertIsArray($jsonData['pagination']);
        self::assertArrayKeysMatch(self::PAGINATION_KEYS, $jsonData['pagination']);
        self::assertSame(1, $jsonData['pagination']['count']);
        self::assertIsArray($jsonData['items']);
        self::assertCount(1, $jsonData['items']);

        self::assertArrayKeysMatch(self::REPORT_RESPONSE_KEYS, $jsonData['items'][0]);
        self::assertEquals($report->reason, $jsonData['items'][0]['reason']);
        self::assertEquals('entry_report', $jsonData['items'][0]['type']);
        self::assertArrayKeysMatch(
            MagazineRetrieveApiTest::MAGAZINE_SMALL_RESPONSE_KEYS,
            $jsonData['items'][0]['magazine']
        );
        self::assertSame($magazine->getId(), $jsonData['items'][0]['magazine']['magazineId']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['reported']);
        self::assertSame($reportedUser->getId(), $jsonData['items'][0]['reported']['userId']);
        self::assertArrayKeysMatch(self::USER_SMALL_RESPONSE_KEYS, $jsonData['items'][0]['reporting']);
        self::assertSame($user->getId(), $jsonData['items'][0]['reporting']['userId']);
        self::assertArrayKeysMatch(self::ENTRY_RESPONSE_KEYS, $jsonData['items'][0]['subject']);
        self::assertSame($entry->getId(), $jsonData['items'][0]['subject']['entryId']);
        self::assertEquals('pending', $jsonData['items'][0]['status']);
        self::assertSame(1, $jsonData['items'][0]['weight']);
        self::assertNull($jsonData['items'][0]['consideredAt']);
        self::assertNull($jsonData['items'][0]['consideredBy']);
        self::assertEquals(
            $report->createdAt->getTimestamp(),
            \DateTimeImmutable::createFromFormat(
                \DateTimeImmutable::ATOM,
                $jsonData['items'][0]['createdAt']
            )->getTimestamp()
        );
    }
}
