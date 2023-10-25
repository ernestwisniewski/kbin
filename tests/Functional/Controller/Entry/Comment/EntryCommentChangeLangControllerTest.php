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

        $form = $crawler->filter('.moderate-panel')->selectButton('change language')->form();

        $this->assertSame($form['lang']['lang']->getValue(), 'en');

        $form['lang']['lang']->select('fr');

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('#main .badge-lang', 'French');
    }
}
