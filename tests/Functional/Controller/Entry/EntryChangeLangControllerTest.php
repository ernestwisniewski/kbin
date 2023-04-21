<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Entity\Entry;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntryChangeLangControllerTest extends WebTestCase
{
    public function testModCanChangeLanguage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $entry = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
        );

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/moderate");

        $this->assertSelectorTextContains('select[name="lang[lang]"] option[selected]', 'english');

        $form = $crawler->filter('.moderate-panel')->selectButton('change language')->form();
        $values = $form['lang']['lang']->availableOptionValues();
        $form['lang']['lang']->select($values[5]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('#main .badge', 'fr');
    }
}
