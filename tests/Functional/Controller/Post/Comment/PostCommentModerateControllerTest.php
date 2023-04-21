<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentModerateControllerTest extends WebTestCase
{
    public function testModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createPostComment('test comment 1');

        $crawler = $client->request('get', "/m/{$comment->magazine->name}/p/{$comment->post->getId()}");
        $client->click($crawler->filter('#post-comment-'.$comment->getId())->selectLink('moderate')->link());

        $this->assertSelectorTextContains('.moderate-panel', 'ban');
    }

    public function testXmlModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $comment = $this->createPostComment('test comment 1');

        $crawler = $client->request('get', "/m/{$comment->magazine->name}/p/{$comment->post->getId()}");
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('#post-comment-'.$comment->getId())->selectLink('moderate')->link());

        $this->assertStringContainsString('moderate-panel', $client->getResponse()->getContent());
    }

    public function testUnauthorizedCanNotShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $comment = $this->createPostComment('test comment 1');

        $client->request('get', "/m/{$comment->magazine->name}/p/{$comment->post->getId()}");
        $this->assertSelectorTextNotContains('#post-comment-'.$comment->getId(), 'moderate');

        $client->request(
            'get',
            "/m/{$comment->magazine->name}/p/{$comment->post->getId()}/-/reply/{$comment->getId()}/moderate"
        );

        $this->assertResponseStatusCodeSame(403);
    }
}
