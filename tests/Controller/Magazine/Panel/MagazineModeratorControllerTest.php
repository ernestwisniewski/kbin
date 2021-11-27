<?php declare(strict_types=1);

namespace App\Tests\Controller\Magazine\Panel;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;
use DateTime;

class MagazineModeratorControllerTest extends WebTestCase
{
    public function testOwnerCanAddModerator()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $this->getUserByUsername('regularUser2');

        $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka');
        $crawler = $client->click($crawler->filter('.kbin-quick-links')->selectLink('Moderatorzy')->link());

        $this->assertCount(1, $crawler->filter('.kbin-magazine-moderators tbody tr'));

        $client->submit(
            $crawler->selectButton('Dodaj')->form(
                [
                    'moderator[user]' => 'regularUser2',
                ]
            )
        );

        $crawler = $client->request('GET', '/m/polityka/panel/moderatorzy');

        $this->assertCount(2, $crawler->filter('.kbin-magazine-moderators tbody tr'));

        $client->submit(
            $crawler->selectButton('Dodaj')->form(
                [
                    'moderator[user]' => 'regularUser2',
                ]
            )
        );

        $this->assertCount(2, $crawler->filter('.kbin-magazine-moderators tbody tr'));
        $this->assertSelectorTextContains('.kbin-magazine-moderator-form', 'Moderator istnieje');
    }

    public function testModeratorCanBanUser()
    {
        $client = $this->createClient();
        $client->loginUser($user2 = $this->getUserByUsername('regularUser2'));

        $manager = static::getContainer()->get(MagazineManager::class);

        $user3 = $this->getUserByUsername('regularUser3');

        $magazine = $this->getMagazineByName('polityka');

        $this->getEntryByTitle('testowa treść', null, 'test', $magazine, $user3);

        $moderatorDto       = new ModeratorDto($magazine);
        $moderatorDto->user = $user2;

        $manager->addModerator($moderatorDto);

        $crawler = $client->request('GET', '/m/polityka');

        $crawler = $client->click($crawler->filter('article .kbin-entry-meta-list-item')->selectLink('zbanuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine_ban[reason]'    => 'spam',
                    'magazine_ban[expiredAt]' => (new DateTime('+1 day'))->format('Y-m-d H:m'),
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertCount(1, $crawler->filter('.kbin-magazine-bans tbody tr'));
    }
}
