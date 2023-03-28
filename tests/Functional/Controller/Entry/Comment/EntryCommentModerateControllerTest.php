<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry\Comment;

use App\Tests\WebTestCase;

class EntryCommentModerateControllerTest extends WebTestCase
{
    public function testModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('test comment 1');

        $crawler = $client->request('get', "/m/{$comment->magazine->name}/t/{$comment->entry->getId()}");
        $client->click($crawler->filter('#entry-comment-'.$comment->getId())->selectLink('moderate')->link());

        $this->assertSelectorTextContains('.moderate-panel', 'ban');
    }

    public function testXmlModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createEntryComment('test comment 1');

        $crawler = $client->request('get', "/m/{$comment->magazine->name}/t/{$comment->entry->getId()}");
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('#entry-comment-'.$comment->getId())->selectLink('moderate')->link());

        $this->assertStringContainsString('moderate-panel', $client->getResponse()->getContent());
    }

    public function testUnauthorizedCanNotShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $comment = $this->createEntryComment('test comment 1');

        $client->request('get', "/m/{$comment->magazine->name}/t/{$comment->entry->getId()}");
        $this->assertSelectorTextNotContains('#entry-comment-'.$comment->getId(), 'moderate');

        $client->request(
            'get',
            "/m/{$comment->magazine->name}/t/{$comment->entry->getId()}/-/comment/{$comment->getId()}/moderate"
        );

        $this->assertResponseStatusCodeSame(403);
    }
}
