<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class PrivacyPolicyControllerTest extends WebTestCase
{
    public function testPrivacyPolicyPage(): void
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('#footer a[href="/privacy-policy"]')->link());

        $this->assertSelectorTextContains('h1', 'Privacy policy');
    }
}
