<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\WebTestCase;

class AwardControllerTest extends WebTestCase
{
    public function testUserCanShowAwardsList(): void
    {
        $client = $this->createClient();

        $client->request('GET', '/awards');

        $this->assertSelectorTextContains('#main .options--top', 'gold');
    }
}
