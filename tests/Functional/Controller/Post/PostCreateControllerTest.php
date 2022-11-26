<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostCreateControllerTest extends WebTestCase
{
    public function testCanCreatePost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $this->getMagazineByName('acme');

        $crawler = $client->request('GET', '/m/acme/wpisy');

        $client->submit(
            $crawler->filter('form[name=post]')->selectButton('Gotowe')->form(
                [
                    'post[body]' => 'Lorem ipsum',
                ]
            )
        );

        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-post-main', 'Lorem ipsum');
    }
}
