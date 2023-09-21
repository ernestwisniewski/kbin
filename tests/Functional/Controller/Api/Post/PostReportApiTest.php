<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api\Post;

use App\Entity\Report;
use App\Repository\MagazineRepository;
use App\Tests\WebTestCase;

class PostReportApiTest extends WebTestCase
{
    public function testApiCannotReportPostAnonymous(): void
    {
        $client = self::createClient();
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test for report', magazine: $magazine);

        $reportRequest = [
            'reason' => 'Test reporting',
        ];

        $client->jsonRequest('POST', "/api/post/{$post->getId()}/report", $reportRequest);
        self::assertResponseStatusCodeSame(401);
    }

    public function testApiCannotReportPostWithoutScope(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test for report', user: $user, magazine: $magazine);

        $reportRequest = [
            'reason' => 'Test reporting',
        ];

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/post/{$post->getId()}/report", $reportRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testApiCanReportPost(): void
    {
        $client = self::createClient();
        $user = $this->getUserByUsername('user');
        $otherUser = $this->getUserByUsername('somebody');
        $magazine = $this->getMagazineByNameNoRSAKey('acme');
        $post = $this->createPost('test for report', user: $otherUser, magazine: $magazine);

        $reportRequest = [
            'reason' => 'Test reporting',
        ];

        $magazineRepository = $this->getService(MagazineRepository::class);

        self::createOAuth2AuthCodeClient();
        $client->loginUser($user);

        $codes = self::getAuthorizationCodeTokenResponse($client, scopes: 'read post:report');
        $token = $codes['token_type'].' '.$codes['access_token'];

        $client->jsonRequest('POST', "/api/post/{$post->getId()}/report", $reportRequest, server: ['HTTP_AUTHORIZATION' => $token]);
        self::assertResponseStatusCodeSame(204);

        $magazine = $magazineRepository->find($magazine->getId());
        $reports = $magazineRepository->findReports($magazine);
        self::assertSame(1, $reports->count());

        /** @var Report $report */
        $report = $reports->getCurrentPageResults()[0];

        self::assertEquals('Test reporting', $report->reason);
        self::assertSame($user->getId(), $report->reporting->getId());
        self::assertSame($otherUser->getId(), $report->reported->getId());
        self::assertSame($post->getId(), $report->getSubject()->getId());
    }
}
