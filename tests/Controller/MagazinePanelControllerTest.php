<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\DTO\ModeratorDto;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\MagazineManager;
use App\Tests\WebTestCase;

class MagazinePanelControllerTest extends WebTestCase
{
    public function testCanEditMagazine()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/panel/edytuj');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[title]' => 'Przepisy kuchenne',
                ]
            )
        );

        $this->assertResponseRedirects('/m/polityka');

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-sidebar .kbin-magazine h2', 'Przepisy kuchenne');
    }

    public function testCannotEditMagazineName()
    {
        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/panel/edytuj');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine[name]'  => 'kuchnia',
                    'magazine[title]' => 'Przepisy kuchenne',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-magazine-header .lead', 'polityka');
        $this->assertSelectorTextNotContains('.kbin-magazine-header .lead', 'kuchnia');
    }

    public function testUnauthorizedUserCannotEditOrPurgeMagazine()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('secondUser'));
        $client->catchExceptions(false);

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/panel/edytuj');

        $this->assertTrue($client->getResponse()->isForbidden());
    }

    public function testOwnerCanAddModerator()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $this->getUserByUsername('regularUser2');

        $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka');
        $crawler = $client->click($crawler->filter('.kbin-quick-links')->selectLink('Moderatorzy')->link());

        $this->assertCount(1, $crawler->filter('.kbin-magazine-moderators tbody tr'));

        $crawler = $client->submit(
            $crawler->selectButton('Dodaj')->form(
                [
                    'moderator[user]' => 'regularUser2',
                ]
            )
        );

        $this->assertCount(2, $crawler->filter('.kbin-magazine-moderators tbody tr'));

        $crawler = $client->submit(
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

        $manager = self::$container->get(MagazineManager::class);

        $user3 = $this->getUserByUsername('regularUser3');

        $magazine = $this->getMagazineByName('polityka');

        $this->getEntryByTitle('testowa treść', null, 'test', $magazine, $user3);

        $moderatorDto = new ModeratorDto($magazine);
        $moderatorDto->setUser($user2);

        $manager->addModerator($moderatorDto);

        $crawler = $client->request('GET', '/m/polityka');

        $crawler = $client->click($crawler->filter('article .kbin-entry-meta-list-item')->selectLink('zbanuj')->link());

        $crawler = $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'magazine_ban[reason]'    => 'spam',
                    'magazine_ban[expiredAt]' => (new \DateTime('+1 day'))->format('Y-m-d H:m'),
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertCount(1, $crawler->filter('.kbin-magazine-bans tbody tr'));
    }

    public function testModeratorCanApproveEntryReport()
    {

    }

    public function testModeratorCanRejectEntryReport() {

    }
}
