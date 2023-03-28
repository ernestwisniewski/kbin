<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentChangeLangControllerTest extends WebTestCase
{
    public function testModCanChangeLanguage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('test comment 1');

        $crawler = $client->request('GET', "/m/acme/t/{$comment->entry->getId()}/-/comment/{$comment->getId()}/moderate");

        $this->assertSelectorTextContains('select[name="lang[lang]"] option[selected]', 'english');

        $form = $crawler->filter('.moderate-panel')->selectButton('change language')->form();
        $values = $form['lang']['lang']->availableOptionValues();
        $form['lang']['lang']->select($values[4]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('select[name="lang[lang]"] option[selected]', 'french');
    }
}
