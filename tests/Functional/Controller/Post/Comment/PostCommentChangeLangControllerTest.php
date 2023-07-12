<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentChangeLangControllerTest extends WebTestCase
{
    public function testModCanChangeLanguage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createPostComment('test comment 1');

        $crawler = $client->request('GET', "/m/acme/p/{$comment->post->getId()}/-/reply/{$comment->getId()}/moderate");

        $this->assertSelectorTextContains('select[name="lang[lang]"] option[selected]', 'English');

        $form = $crawler->filter('.moderate-panel')->selectButton('change language')->form();
        $values = $form['lang']['lang']->availableOptionValues();
        $form['lang']['lang']->select($values[array_search('fr', $values)]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('#main .badge', 'fr');
    }
}
