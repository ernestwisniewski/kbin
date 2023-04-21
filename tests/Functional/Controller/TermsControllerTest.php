<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class TermsControllerTest extends WebTestCase
{
    public function testTermsPage(): void
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('#footer a[href="/terms"]')->link());

        $this->assertSelectorTextContains('h1', 'Terms');
    }
}
