<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class MagazineControllerTest extends WebTestCase
{
    public function testCanCreateMagazine()
    {
        $client = static::createUserClient();
        $crawler = $client->request('GET', '/nowyMagazyn');

        dd($crawler);
    }
}
