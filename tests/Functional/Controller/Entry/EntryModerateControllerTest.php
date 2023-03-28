<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Entity\Contracts\VotableInterface;
use App\Service\VoteManager;
use App\Tests\WebTestCase;

class EntryModerateControllerTest extends WebTestCase
{
    public function testModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('get', '/');
        $client->click($crawler->filter('#entry-'.$entry->getId())->selectLink('moderate')->link());

        $this->assertSelectorTextContains('.moderate-panel', 'ban');
    }

    public function testXmlModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $crawler = $client->request('get', '/');
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('#entry-'.$entry->getId())->selectLink('moderate')->link());

        $this->assertStringContainsString('moderate-panel', $client->getResponse()->getContent());
    }

    public function testUnauthorizedCanNotShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('test entry 1', 'https://kbin.pub');

        $client->request('get', "/m/{$entry->magazine->name}/t/{$entry->getId()}");
        $this->assertSelectorTextNotContains('#entry-'.$entry->getId(), 'moderate');

        $client->request(
            'get',
            "/m/{$entry->magazine->name}/t/{$entry->getId()}/-/moderate"
        );

        $this->assertResponseStatusCodeSame(403);
    }
}
