<?php declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Service\UserManager;
use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserFollowControllerTest extends WebTestCase
{
    public function testCanShowPublicProfile()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $entry = $this->getEntryByTitle('treść1');
        $entry = $this->getEntryByTitle('treść2');

        $crawler = $client->request('GET', '/u/regularUser');

        $this->assertCount(2, $crawler->filter('article.kbin-entry'));
    }

    public function testUserCanFollow()
    {
        $client  = $this->createClient();
        $manager = static::getContainer()->get(UserManager::class);

        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');
        $user4 = $this->getUserByUsername('regularUser4');

        $magazine  = $this->getMagazineByName('polityka', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);

        $this->getEntryByTitle('treść 1', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 2', null, null, $magazine2, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine, $user4);
        $this->getEntryByTitle('treść 6', null, null, $magazine2, $user4);

        $manager->follow($user3, $user2);

        $crawler = $client->request('GET', '/u/regularUser2');

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '1');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-sub button')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '2');

        $crawler = $client->request('GET', '/sub');

        $this->assertSelectorTextContains('.kbin-entry-title', 'treść 1');
        $this->assertCount(2, $crawler->filter('.kbin-entry-title'));

        $crawler = $client->request('GET', '/u/regularUser2');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-sub button')->selectButton('obserwuj')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '1');
    }

    public function testUserCanBlock() //@todo
    {
        $client  = $this->createClient();
        $manager = static::getContainer()->get(UserManager::class);

        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');
        $user4 = $this->getUserByUsername('regularUser4');

        $magazine  = $this->getMagazineByName('polityka', $user2);
        $magazine2 = $this->getMagazineByName('kuchnia', $user2);

        $this->getEntryByTitle('treść 1', null, null, $magazine, $user2);
        $this->getEntryByTitle('treść 2', null, null, $magazine2, $user2);
        $this->getEntryByTitle('treść 3', null, null, $magazine, $user3);
        $this->getEntryByTitle('treść 4', null, null, $magazine2, $user3);
        $this->getEntryByTitle('treść 5', null, null, $magazine, $user4);
        $this->getEntryByTitle('treść 6', null, null, $magazine2, $user4);

        $manager->follow($user, $user2);

        $crawler = $client->request('GET', '/u/regularUser2');

        $client->submit(
            $crawler->filter('.kbin-entry-info-user .kbin-user-block button')->selectButton('')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('kbin-block--active', $crawler->filter('.kbin-user-block')->attr('class'));
        $this->assertSelectorTextContains('.kbin-entry-info-user .kbin-sub', '0');
    }
}
