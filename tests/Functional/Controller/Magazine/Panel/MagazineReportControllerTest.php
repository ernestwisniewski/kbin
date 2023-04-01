<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\DTO\ReportDto;
use App\Service\ReportManager;
use App\Tests\WebTestCase;

class MagazineReportControllerTest extends WebTestCase
{
    public function testModCanSeeEntryReports(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));
        $user2 = $this->getUserByUsername('JaneDoe');

        $entryComment = $this->createEntryComment('Test comment 1');
        $postComment = $this->createPostComment('Test post 1');

        foreach ([$entryComment, $postComment, $entryComment->entry, $postComment->post] as $subject) {
            $this->getContainer()->get(ReportManager::class)->report(
                (new ReportDto())->create($subject, 'test reason'),
                $user
            );
        }

        $client->request('GET', '/');
        $crawler = $client->request('GET', '/m/acme/panel/reports');

        $this->assertSelectorTextContains('#main .options__main a.active', 'reports');
        $this->assertEquals(
            4,
            $crawler->filter('#main .report')->count()
        );
    }

    public function testUnauthorizedUserCannotSeeReports(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/reports');

        $this->assertResponseStatusCodeSame(403);
    }
}
