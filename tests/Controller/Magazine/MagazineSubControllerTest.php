<?php declare(strict_types=1);

namespace App\Tests\Controller\Magazine;

use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class MagazineSubControllerTest extends WebTestCase
{
    public function testUserCanSubscribeMagazine()
    {
        $client  = $this->createClient();
        $manager = static::getContainer()->get(MagazineManager::class);
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

        $magazine  = $this->getMagazineByName('polityka', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);
        $magazine3 = $this->getMagazineByName('muzyka', $user2);

        $this->getEntryByTitle('treść 2', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine3, $user2);
        $this->getEntryByTitle('treść 4', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine3, $user);
        $this->getEntryByTitle('treść 1', null, null, $magazine, $user);

        $manager->subscribe($magazine, $user3);

        $crawler = $client->request('GET', '/m/polityka');

        $this->assertSelectorTextContains('.kbin-magazine-header .kbin-sub', '2');

        $client->submit(
            $crawler->filter('.kbin-magazine-header .kbin-sub')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .kbin-sub', '3');

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść ');
        $this->assertCount(3, $crawler->filter('.kbin-entry-title'));

        $crawler = $client->click($crawler->filter('.kbin-entry-title a')->link());

        $client->submit(
            $crawler->filter('.kbin-magazine-header .kbin-sub')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .kbin-sub', '2');

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść 5');
        $this->assertCount(2, $crawler->filter('.kbin-entry-title'));
    }
}
