<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Tests\WebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class RegisterControllerTest extends WebTestCase
{
    public function testUserCanVerifyAccount(): void
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
                    'email'    => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextNOTContains('.kbn-login-btn', 'Zaloguj się');
    }

    private function registerUserAccount(KernelBrowser $client)
    {
        $crawler = $client->request('GET', '/');
        $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();
        $crawler = $client->click($crawler->filter('.kbin-login')->selectLink('Zarejestruj się.')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'user_register[username]'              => 'Ernest',
                    'user_register[email]'                 => 'ernest@karab.in',
                    'user_register[plainPassword][first]'  => 'secret',
                    'user_register[plainPassword][second]' => 'secret',
                    'user_register[agreeTerms]'            => true,
                ]
            )
        );

        return $crawler;
    }

    public function testUserCannotLoginWithoutConfirmation()
    {
        $client = $this->createClient();

        $this->registerUserAccount($client);

        $crawler = $client->followRedirect();

        $client->click($crawler->filter('.kbn-login-btn')->selectLink('Zaloguj się')->link());
        $crawler = $client->followRedirect();

        $client->submit(
            $crawler->selectButton('Zaloguj się')->form(
                [
                    'email'    => 'ernest@karab.in',
                    'password' => 'secret',
                ]
            )
        );

        $client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', 'Twoje konto nie jest aktywne.');
    }
}
