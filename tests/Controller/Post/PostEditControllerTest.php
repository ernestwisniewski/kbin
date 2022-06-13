<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostEditControllerTest extends WebTestCase
{
    public function testCanEditPost(): void
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->createPost('example content');

        $crawler = $client->request('GET', "/m/acme/wpisy");
        $crawler = $client->click($crawler->filter('.kbin-post-meta')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'post[body]' => 'zmieniona treść',
                ]
            )
        );

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-post-main', 'zmieniona treść');
    }

    public function testUnauthorizedUserCannotEditPost(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($this->getUserByUsername('JaneDoe'));

        $post = $this->createPost('example content');

        $client->request('GET', "/m/acme/w/{$post->getId()}/-/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
