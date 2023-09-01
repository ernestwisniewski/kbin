<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentCreateControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->kibbyPath = dirname(__FILE__, 5).'/assets/kibby_emoji.png';
    }

    public function testUserCanCreatePostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('GET', '/m/acme/p/'.$post->getId().'/test-post-1');

        $client->submit(
            $crawler->filter('form[name=post_comment]')->selectButton('Add comment')->form(
                [
                    'post_comment[body]' => 'test comment 1',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/p/'.$post->getId().'/test-post-1');
        $client->followRedirect();

        $this->assertSelectorTextContains('#main .comments', 'test comment 1');
    }

    public function testUserCanCreatePostCommentWithImage(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/test-post-1");

        $form = $crawler->filter('form[name=post_comment]')->selectButton('Add comment')->form();
        $form->get('post_comment[body]')->setValue('Test comment 1');
        $form->get('post_comment[image]')->upload($this->kibbyPath);
        // Needed since we require this global to be set when validating entries but the client doesn't actually set it
        $_FILES = $form->getPhpFiles();
        $client->submit($form);

        $this->assertResponseRedirects("/m/acme/p/{$post->getId()}/test-post-1");
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('#main .comments', 'Test comment 1');
        $this->assertSelectorExists('.comments footer figure img');
        $imgSrc = $crawler->filter('.comments footer figure img')->getNode(0)->attributes->getNamedItem('src')->textContent;
        $this->assertStringContainsString(self::KIBBY_PNG_URL_RESULT, $imgSrc);
        $_FILES = [];
    }

    public function testUserCannotCreateInvalidPostComment(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $post = $this->createPost('test post 1');

        $crawler = $client->request('GET', '/m/acme/p/'.$post->getId().'/test-post-1');

        $crawler = $client->submit(
            $crawler->filter('form[name=post_comment]')->selectButton('Add comment')->form(
                [
                    'post_comment[body]' => '',
                ]
            )
        );

        $this->assertSelectorTextContains('#content', 'This value should not be blank.');
    }
}
