<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class AboutControllerTest extends WebTestCase
{
    public function testAbout(): void
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/ocb');

        $this->assertStringContainsString('Portal społecznościowy, który ma sens', $crawler->filter('.kbin-about')->html());
    }
}
