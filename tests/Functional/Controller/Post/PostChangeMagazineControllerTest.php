<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostChangeMagazineControllerTest extends WebTestCase
{
    public function testAdminCanChangeMagazine(): void
    {
        $client = $this->createClient();

        $user = $this->getUserByUsername('JohnDoe');
        $this->setAdmin($user);
        $client->loginUser($user);

        $this->getMagazineByName('kbin');

        $post = $this->createPost(
            'test post 1',
        );

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/-/moderate");

        $client->submit(
            $crawler->filter('form[name=change_magazine]')->selectButton('change magazine')->form(
                [
                    'change_magazine[new_magazine]' => 'kbin',
                ]
            )
        );

        $client->followRedirect();
        $client->followRedirect();

        $this->assertSelectorTextContains('.head-title', 'kbin');
    }

    public function testUnauthorizedUserCantChangeMagazine(): void
    {
        $client = $this->createClient();

        $client->loginUser($this->getUserByUsername('JohnDoe'));

        $this->getMagazineByName('kbin');

        $entry = $this->createPost(
            'test post 1',
        );

        $client->request('GET', "/m/acme/p/{$entry->getId()}/-/moderate");

        $this->assertSelectorTextNotContains('.moderate-panel', 'change magazine');
    }
}
