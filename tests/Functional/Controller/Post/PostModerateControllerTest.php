<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostModerateControllerTest extends WebTestCase
{
    public function testModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('get', '/microblog');
        $client->click($crawler->filter('#post-'.$post->getId())->selectLink('moderate')->link());

        $this->assertSelectorTextContains('.moderate-panel', 'ban');
    }

    public function testXmlModCanShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('get', '/microblog');
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->click($crawler->filter('#post-'.$post->getId())->selectLink('moderate')->link());

        $this->assertStringContainsString('moderate-panel', $client->getResponse()->getContent());
    }

    public function testUnauthorizedCanNotShowPanel(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $post = $this->createPost('test post 1');

        $client->request('get', "/m/{$post->magazine->name}/p/{$post->getId()}");
        $this->assertSelectorTextNotContains('#post-'.$post->getId(), 'moderate');

        $client->request(
            'get',
            "/m/{$post->magazine->name}/p/{$post->getId()}/-/moderate"
        );

        $this->assertResponseStatusCodeSame(403);
    }
}
