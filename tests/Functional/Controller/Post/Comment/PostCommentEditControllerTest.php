<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentEditControllerTest extends WebTestCase
{
    public function testAuthorCanEditOwnPostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');
        $this->createPostComment('test comment 1', $post);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $crawler = $client->click($crawler->filter('#main .post-comment')->selectLink('edit')->link());

        $this->assertSelectorExists('#main .post-comment');
        $this->assertSelectorTextContains('textarea[name="post_comment[body]"]', 'test comment 1');

        $client->submit(
            $crawler->filter('form[name=post_comment]')->selectButton('Save changes')->form(
                [
                    'post_comment[body]' => 'test comment 2 body',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main .post-comment', 'test comment 2 body');
    }

    public function testAuthorCanEditOwnPostCommentWithImage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');
        $this->createPostComment('test comment 1', $post, imageDto: $this->getKibbyImageDto());

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $crawler = $client->click($crawler->filter('#main .post-comment')->selectLink('edit')->link());

        $this->assertSelectorExists('#main .post-comment');
        $this->assertSelectorTextContains('textarea[name="post_comment[body]"]', 'test comment 1');
        $this->assertSelectorExists('#main .post-comment img');
        $node = $crawler->selectImage('kibby')->getNode(0);
        $this->assertNotNull($node);
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $node->attributes->getNamedItem('src')->textContent);

        $client->submit(
            $crawler->filter('form[name=post_comment]')->selectButton('Save changes')->form(
                [
                    'post_comment[body]' => 'test comment 2 body',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('#main .post-comment', 'test comment 2 body');
        $this->assertSelectorExists('#main .post-comment img');
        $node = $crawler->selectImage('kibby')->getNode(0);
        $this->assertNotNull($node);
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $node->attributes->getNamedItem('src')->textContent);
    }
}
