<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Magazine\Panel;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;
use DateTime;

class MagazineModeratorControllerTest extends WebTestCase
{
    public function testOwnerCanAddModerator(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $this->getUserByUsername('JaneDoe');

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme');
        $crawler = $client->click($crawler->filter('.kbin-magazine-panel')->selectLink('Moderatorzy')->link());

        $this->assertCount(1, $crawler->filter('.kbin-magazine-moderators tbody tr'));

        $client->submit(
            $crawler->selectButton('Dodaj')->form(
                [
                    'moderator[user]' => 'JaneDoe',
                ]
            )
        );

        $crawler = $client->request('GET', '/m/acme/panel/moderatorzy');

        $this->assertCount(2, $crawler->filter('.kbin-magazine-moderators tbody tr'));

        $client->submit(
            $crawler->selectButton('Dodaj')->form(
                [
                    'moderator[user]' => 'JaneDoe',
                ]
            )
        );

        $this->assertCount(2, $crawler->filter('.kbin-magazine-moderators tbody tr'));
        $this->assertSelectorTextContains('.kbin-magazine-moderator-form', 'Moderator istnieje');
    }

    public function testModeratorCanBanUser(): void
    {
        $client = $this->createClient();
        $client->loginUser($user2 = $this->getUserByUsername('JaneDoe'));

        $manager = static::getContainer()->get(MagazineManager::class);

        $user3 = $this->getUserByUsername('MaryJane');

        $magazine = $this->getMagazineByName('acme');

        $this->getEntryByTitle('testowa treść', null, 'test', $magazine, $user3);

        $moderatorDto       = new ModeratorDto($magazine);
        $moderatorDto->user = $user2;

        $manager->addModerator($moderatorDto);

        $crawler = $client->request('GET', '/m/acme');

        $crawler = $client->click($crawler->filter('article .kbin-entry-meta-list-item')->selectLink('zbanuj')->link());

        $client->submit(
            $crawler->filter('form[name=magazine_ban]')->selectButton('Gotowe')->form(
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
