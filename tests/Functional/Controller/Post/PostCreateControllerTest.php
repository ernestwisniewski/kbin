<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostCreateControllerTest extends WebTestCase
{
    public function testUserCanCreatePost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/microblog');

        $client->submit(
            $crawler->filter('form[name=post]')->selectButton('Add post')->form(
                [
                    'post[body]' => 'test post 1',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/microblog/newest');
        $client->followRedirect();

        $this->assertSelectorTextContains('#content .post', 'test post 1');
    }

    public function testUserCannotCreateInvalidPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/microblog');

        $crawler = $client->submit(
            $crawler->filter('form[name=post]')->selectButton('Add post')->form(
                [
                    'post[body]' => 't',
                ]
            )
        );

        $this->assertSelectorTextContains('#content', 'This value is too short. It should have 2 characters or more.');
    }
}
