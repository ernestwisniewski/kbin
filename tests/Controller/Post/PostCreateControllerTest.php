<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\Tests\WebTestCase;

class PostCreateControllerTest extends WebTestCase
{
    public function testCanCreatePost()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('user'));

        $magazine = $this->getMagazineByName('polityka');

        $crawler = $client->request('GET', '/m/polityka/wpisy');

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'post[body]' => 'Lorem ipsum',
                ]
            )
        );

        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-post-main', 'Lorem ipsum');
    }
}
