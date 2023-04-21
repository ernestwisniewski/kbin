<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine;

use App\Tests\WebTestCase;

class MagazineCreateControllerTest extends WebTestCase
{
    public function testUserCanCreateMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/newMagazine');

        $client->submit(
            $crawler->filter('form[name=magazine]')->selectButton('Create new magazine')->form(
                [
                    'magazine[name]' => 'TestMagazine',
                    'magazine[title]' => 'Test magazine title',
                ]
            )
        );

        $this->assertResponseRedirects('/m/TestMagazine');

        $client->followRedirect();

        $this->assertSelectorTextContains('header .head-title', '/m/TestMagazine');
        $this->assertSelectorTextContains('#content', 'Empty');
    }

    public function testUserCantCreateInvalidMagazine(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $crawler = $client->request('GET', '/newMagazine');

        $client->submit(
            $crawler->filter('form[name=magazine]')->selectButton('Create new magazine')->form(
                [
                    'magazine[name]' => 't',
                    'magazine[title]' => 'Test magazine title',
                ]
            )
        );

        $this->assertSelectorTextContains('#content', 'This value is too short. It should have 2 characters or more.');
    }

    public function testUserCantCreateTwoSameMagazines(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/newMagazine');

        $client->submit(
            $crawler->filter('form[name=magazine]')->selectButton('Create new magazine')->form(
                [
                    'magazine[name]' => 'acme',
                    'magazine[title]' => 'Test magazine title',
                ]
            )
        );

        $this->assertSelectorTextContains('#content', 'This value is already used.');
    }
}
