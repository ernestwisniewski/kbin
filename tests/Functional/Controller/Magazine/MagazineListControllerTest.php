<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class MagazineListControllerTest extends WebTestCase
{
    /** @dataProvider magazines */
    public function testMagazineListIsFiltered(array $queryParams, array $expectedMagazines): void
    {
        $client = $this->createClient();

        $this->loadExampleMagazines();

        $crawler = $client->request('GET', '/magazines');

        $crawler = $client->submit(
            $crawler->filter('form[method=get]')->selectButton('')->form($queryParams)
        );

        $this->assertSame(
            $expectedMagazines,
            $crawler->filter('#content .table-responsive .magazine-inline')->each(fn (Crawler $node) => $node->innerText()),
        );
    }

    public function magazines(): iterable
    {
        return [
            [['query' => 'test'], []],
            [['query' => 'acme'], ['acme']],
            [['query' => '', 'adult' => 'only'], ['adult']],
            [['query' => 'acme', 'adult' => 'only'], []],
            [['query' => 'foobar', 'fields' => 'names_descriptions'], ['acme']],
            [['adult' => 'show'], ['acme', 'kbin', 'adult', 'starwarsmemes@republic.new']],
            [['federation' => 'local'], ['acme', 'kbin', 'adult']],
            [['query' => 'starwars', 'federation' => 'local'], []],
            [['query' => 'starwars', 'federation' => 'all'], ['starwarsmemes@republic.new']],
            [['query' => 'trap', 'fields' => 'names_descriptions'], ['starwarsmemes@republic.new']],
        ];
    }
}
