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
                    'post[body]' => '',
                ]
            )
        );

        $this->assertSelectorTextContains('#content', 'This value should not be blank.');
    }

    public function testCreatedPostIsMarkedAsForAdults(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', hideAdult: false));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/microblog');

        $client->submit(
            $crawler->filter('form[name=post]')->selectButton('Add post')->form(
                [
                    'post[body]' => 'test nsfw 1',
                    'post[isAdult]' => '1',
                ]
            )
        );

        $this->assertResponseRedirects('/m/acme/microblog/newest');
        $client->followRedirect();

        $this->assertSelectorTextContains('blockquote header .danger', '+18');
    }

    public function testPostCreatedInAdultMagazineIsAutomaticallyMarkedAsForAdults(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe', hideAdult: false));

        $this->getMagazineByName('adult', isAdult: true);

        $crawler = $client->request('GET', '/m/adult/microblog');

        $client->submit(
            $crawler->filter('form[name=post]')->selectButton('Add post')->form(
                [
                    'post[body]' => 'test nsfw 1',
                ]
            )
        );

        $this->assertResponseRedirects('/m/adult/microblog/newest');
        $client->followRedirect();

        $this->assertSelectorTextContains('blockquote header .danger', '+18');
    }
}
