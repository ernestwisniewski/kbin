<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;

class EntryCreateControllerTest extends WebTestCase
{
    public function testUserCanCreateEntryLink()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $client->request('GET', '/m/acme/new');

        $this->assertSelectorExists('form[name=entry_link]');
    }

    public function testUserCanCreateEntryLinkFromMagazinePage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/new');

        $client->submit(
            $crawler->filter('form[name=entry_link]')->selectButton('Add new link')->form(
                [
                    'entry_link[url]' => 'https://kbin.pub',
                    'entry_link[title]' => 'Test entry 1',
                    'entry_link[body]' => 'Test body',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/newest');
        $client->followRedirect();

        $this->assertSelectorTextContains('article h2', 'Test entry 1');
    }

    public function testUserCanCreateEntryArticle()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $client->request('GET', '/m/acme/new/article');

        $this->assertSelectorExists('form[name=entry_article]');
    }

    public function testUserCanCreateEntryArticleFromMagazinePage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/new/article');

        $client->submit(
            $crawler->filter('form[name=entry_article]')->selectButton('Add new article')->form(
                [
                    'entry_article[title]' => 'Test entry 1',
                    'entry_article[body]' => 'Test body',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/newest');
        $client->followRedirect();

        $this->assertSelectorTextContains('article h2', 'Test entry 1');
    }

    public function testUserCanCreateEntryPhoto()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $client->request('GET', '/m/acme/new/photo');

        $this->assertSelectorExists('form[name=entry_image]');
    }

    public function testUserCanCreateEntryPhotoFromMagazinePage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/new/photo');

        $this->assertSelectorExists('form[name=entry_image]');
    }
}
