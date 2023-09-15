<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\Tests\WebTestCase;

class MagazineAppearanceControllerTest extends WebTestCase
{
    public function testOwnerCanEditMagazineTheme(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/panel/appearance');
        $this->assertSelectorTextContains('#main .options__main a.active', 'appearance');
        $form = $crawler->filter('#main form[name=magazine_theme]')->selectButton('Done')->form();
        $form['magazine_theme[icon]']->upload($this->kibbyPath);
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#sidebar .magazine img');
        $node = $crawler->filter('#sidebar .magazine img')->getNode(0);
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $node->attributes->getNamedItem('src')->textContent);
    }

    public function testOwnerCanEditMagazineCSS(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));
        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/panel/appearance');
        $this->assertSelectorTextContains('#main .options__main a.active', 'appearance');
        $form = $crawler->filter('#main form[name=magazine_theme]')->selectButton('Done')->form();
        $form['magazine_theme[customCss]']->setValue('#middle { display: none; }');
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
    }

    public function testUnauthorizedUserCannotEditMagazineTheme(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $this->getMagazineByName('acme');

        $client->request('GET', '/m/acme/panel/appearance');

        $this->assertResponseStatusCodeSame(403);
    }
}
