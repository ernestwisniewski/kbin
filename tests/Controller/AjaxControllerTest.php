<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\DTO\MagazineBanDto;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Tests\WebTestCase;

class AjaxControllerTest extends WebTestCase
{
    public function testFetchEntryArticle()
    {
        $client = $this->createClient();

        $entry = $this->getEntryByTitle('Lorem ipsum', null, 'dolor sit amet');

        $client->jsonRequest('GET', '/ajax/fetch_entry/' . $entry->getId());

        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }

    public function testFetchEntryLink()
    {
        $client = $this->createClient();

        $entry = $this->getEntryByTitle('Lorem ipsum', 'https://youtube.com');

        $client->jsonRequest('GET', '/ajax/fetch_entry/' . $entry->getId());

        $this->assertStringContainsString('Lorem ipsum', $client->getResponse()->getContent());
    }
}
