<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SecurityControllerTest extends WebTestCase
{
    public function testUserCanVerifyAccount()
    {
        $client = $this->createClient();

        $crawler = $this->registerUserAccount($client);

        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage(0);

        $this->assertEmailHeaderSame($email, 'To', 'ernest@karab.in');

        $verifyLink = $email->getContext()['signedUrl'];

        $crawler = $client->request('GET', $verifyLink);
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email'         => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbn-login-btn', 'Profil');
    }

    public function testUserCannotLoginWithoutConfirmation()
    {
        $client = $this->createClient();

        $crawler = $this->registerUserAccount($client);

        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email'         => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', 'Twoje konto nie jest aktywne.');
    }

    private function registerUserAccount(KernelBrowser $client)
    {
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();
        $crawler = $client->click($crawler->filter('.kbin-login')->selectLink('Zarejestruj się!')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'user[username]'              => 'Ernest',
                    'user[email]'                 => 'ernest@karab.in',
                    'user[plainPassword][first]'  => 'secret',
                    'user[plainPassword][second]' => 'secret',
                    'user[agreeTerms]'            => true,
                ]
            )
        );

        return $crawler;
    }
}
