<?php declare(strict_types=1);

namespace App\Tests\ApiDataProvider;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class MagazineCollectionDataProviderTest extends ApiTestCase
{
    public function testMagazineCollection(): void
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/api/magazines');
    }
}
