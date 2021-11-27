<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryEditControllerTest extends WebTestCase
{
    public function testCanEditLink()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc', 'https://wp.pl');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/komentarze");
        $crawler = $client->click($crawler->filter('.kbin-entry-meta')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $entry->getMagazine()->getId(),
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
    }

    public function testCanEditArticle()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc', null, 'przykładowa treść wpisu');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'zmieniona treść',
                    'entry_article[body]'     => 'zmieniona treść wpisu',
                    'entry_article[magazine]' => $entry->magazine->getId(),
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
        $this->assertSelectorTextContains('.kbin-entry-content p', 'zmieniona treść wpisu');
    }

    public function testCannotEditEntryMagazine()
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $entry = $this->getEntryByTitle('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $this->getMagazineByName('test')->getId(),
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testUnauthorizedUserCannotEditEntry()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser2'));

        $entry = $this->getEntryByTitle('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/t/{$entry->getId()}/-/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
