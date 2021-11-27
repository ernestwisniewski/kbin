<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\Tests\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostEditControllerTest extends WebTestCase
{
    public function testCanEditPost()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $post = $this->createPost('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/wpisy");
        $crawler = $client->click($crawler->filter('.kbin-post-meta')->selectLink('edytuj')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'post[body]' => 'zmieniona treść',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kbin-post-main', 'zmieniona treść');
    }

    public function testUnauthorizedUserCannotEditPost()
    {
        $this->expectException(AccessDeniedException::class);

        $client = $this->createClient();
        $client->catchExceptions(false);
        $client->loginUser($user = $this->getUserByUsername('regularUser2'));

        $post = $this->createPost('przykladowa tresc');

        $crawler = $client->request('GET', "/m/polityka/w/{$post->getId()}/-/edytuj");

        $this->assertTrue($client->getResponse()->isServerError());
    }
}
