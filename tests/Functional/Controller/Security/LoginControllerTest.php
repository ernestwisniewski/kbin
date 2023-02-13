<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Tests\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testUserCanLogin(): void
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('JohnDoe');

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('header')->selectLink('Log in')->link());

        $client->submit(
            $crawler->selectButton('Log in')->form(
                [
                    'email' => 'JohnDoe',
                    'password' => 'secret',
                ]
            )
        );

        $crawler = $client->followRedirect();

//        $this->assertSelectorTextNotContains('header', 'Log in'); // @todo
    }
}
