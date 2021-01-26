<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testCanShowPublicProfile()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $entry = $this->getEntryByTitle('treść1');
        $entry = $this->getEntryByTitle('treść2');

        $crawler = $client->request('GET', '/u/regularUser');

        $this->assertCount(2,$crawler->filter('.kbin-entry-list-item'));
    }
}
