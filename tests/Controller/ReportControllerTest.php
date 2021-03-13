<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Contracts\VoteInterface;
use App\Tests\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    public function testCanAddEntryReport()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('regularUser'));

        $user1 = $this->getUserByUsername('regularUser');
        $this->getEntryByTitle('testowy wpis', null, null, null, $user1);

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbin-entry-list .kbin-entry-meta')->selectLink('zgłoś')->link());

        $crawler = $client->submit(
            $crawler->filter('.kbin-report-page')->selectButton('Gotowe')->form()
        );


    }
}
