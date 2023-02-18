<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Domain;

use App\Tests\WebTestCase;

class DomainFrontControllerTest extends WebTestCase
{
    public function testDomainCommentFrontPage(): void
    {
        $client = $this->createClient();

        $this->createEntry(
            'test entry 1',
            $this->getMagazineByName('acme'),
            $this->getUserByUsername('JohnDoe'),
            'http://kbin.pub/instances'
        );

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('#content article')->selectLink('kbin.pub')->link());

        $this->assertSelectorTextContains('#header', '/d/kbin.pub');
        $this->assertSelectorTextContains('.entry__meta', 'JohnDoe');
        $this->assertSelectorTextContains('.entry__meta', 'to acme');

        foreach ($this->getSortOptions() as $sortOption) {
            $crawler = $client->click($crawler->filter('.options__main')->selectLink($sortOption)->link());
            $this->assertSelectorTextContains('.options__main', $sortOption);
            $this->assertSelectorTextContains('h1', 'kbin.pub');
            $this->assertSelectorTextContains('h2', ucfirst($sortOption));
        }
    }

    private function getSortOptions(): array
    {
        return ['top', 'hot', 'newest', 'active', 'commented'];
    }
}
