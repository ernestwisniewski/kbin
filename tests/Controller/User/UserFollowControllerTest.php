<?php declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Service\UserManager;
use App\Tests\WebTestCase;

class UserFollowControllerTest extends WebTestCase
{
    public function testCanShowPublicProfile(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $entry = $this->getEntryByTitle('treść1');
        $entry = $this->getEntryByTitle('treść2');

        $crawler = $client->request('GET', '/u/JohnDoe');

        $this->assertCount(2, $crawler->filter('article.kbin-entry'));
    }

    public function testUserCanFollow(): void
    {
        $client  = $this->createClient();
        $manager = static::getContainer()->get(UserManager::class);

        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');
        $user4 = $this->getUserByUsername('PeterParker');

        $magazine  = $this->getMagazineByName('acme', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);

        $this->getEntryByTitle('treść 1', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine, $user4);
        $this->getEntryByTitle('treść 6', null, null, $magazine2, $user4);
        $this->getEntryByTitle('treść 2', null, null, $magazine2, $user2);

        $manager->follow($user3, $user2);

        $crawler = $client->request('GET', '/u/JaneDoe');

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '1');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-sub button')->selectButton('obserwuj')->form()
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '2');

        $crawler = $client->request('GET', '/sub/najnowsze');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść 2 (karab.in)');
        $this->assertCount(2, $crawler->filter('.kbin-entry-title'));

        $crawler = $client->request('GET', '/u/JaneDoe');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-sub button')->selectButton('obserwuj')->form()
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '1');
    }

    public function testUserCanBlock(): void //@todo
    {
        $client  = $this->createClient();
        $manager = static::getContainer()->get(UserManager::class);

        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');
        $user4 = $this->getUserByUsername('PeterParker');

        $magazine  = $this->getMagazineByName('acme', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);

        $this->getEntryByTitle('treść 1', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 2', null, null, $magazine2, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine, $user4);
        $this->getEntryByTitle('treść 6', null, null, $magazine2, $user4);

        $manager->follow($user, $user2);

        $crawler = $client->request('GET', '/u/JaneDoe');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-user-block button')->selectButton('')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('kbin-block--active', $crawler->filter('.kbin-user-block')->attr('class'));
        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '0');
    }
}
