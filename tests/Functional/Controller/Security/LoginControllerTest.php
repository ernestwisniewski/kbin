<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Tests\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testUserCanLogin(): void
    {
        $client = RegisterControllerTest::register(true);

        $crawler = $client->request('get', '/');
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

        $this->assertSelectorTextContains('#header', 'JohnDoe');
    }

    public function testUserCannotLoginWithoutActivation(): void
    {
        $client = RegisterControllerTest::register();

        $crawler = $client->request('get', '/');
        $crawler = $client->click($crawler->filter('header')->selectLink('Log in')->link());

        $client->submit(
            $crawler->selectButton('Log in')->form(
                [
                    'email' => 'JohnDoe',
                    'password' => 'secret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('#main', 'Please check your email and click on the activation link');
    }

    public function testUserCantLoginWithWrongPassword(): void
    {
        $client = $this->createClient();
        $this->getUserByUsername('JohnDoe');

        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('header')->selectLink('Log in')->link());

        $client->submit(
            $crawler->selectButton('Log in')->form(
                [
                    'email' => 'JohnDoe',
                    'password' => 'wrongpassword',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.alert__danger', 'Invalid credentials.'); // @todo
    }
}
