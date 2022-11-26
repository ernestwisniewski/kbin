<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EntryEditControllerTest extends WebTestCase
{
    public function testCanEditLink(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('example content', 'https://wp.pl');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/komentarze");
        $crawler = $client->click($crawler->filter('.kbin-entry-meta')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->filter('form[name=entry_link]')->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $entry->getMagazine()->getId(),
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
    }

    public function testCanEditArticle(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('example content', null, 'example post content');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->filter('form[name=entry_article]')->selectButton('Gotowe')->form(
                [
                    'entry_article[title]'    => 'zmieniona treść',
                    'entry_article[body]'     => 'zmieniona treść wpisu',
                    'entry_article[magazine]' => $entry->magazine->getId(),
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-entry-title', 'zmieniona treść');
        $this->assertSelectorTextContains('.kbin-entry-content p', 'zmieniona treść wpisu');
    }

    public function testCannotEditEntryMagazine(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle('example content');

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/edytuj");

        $client->submit(
            $crawler->filter('form[name=entry_link]')->selectButton('Gotowe')->form(
                [
                    'entry_link[title]'    => 'zmieniona treść',
                    'entry_link[url]'      => 'https://wp.pl',
                    'entry_link[magazine]' => $this->getMagazineByName('test')->getId(),
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isServerError());
    }

    public function testUnauthorizedUserCannotEditEntry(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('JaneDoe'));

        $entry = $this->getEntryByTitle('example content');

        $client->request('GET', "/m/acme/t/{$entry->getId()}/-/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
