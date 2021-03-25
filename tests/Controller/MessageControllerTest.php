<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Contracts\VoteInterface;
use App\Tests\WebTestCase;

class MessageControllerTest extends WebTestCase
{
    public function testUserCanSendMessage()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $u1 = $this->getUserByUsername('testUser1');

        // First thread
        $crawler = $client->request('GET', '/u/testUser1');
        $crawler = $client->click($crawler->filter('.kbin-entry-info')->selectLink('Wyślij wiadomość')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'message[body]' => 'Testowa wiadomość.',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-profile-threads-page', '0 odpowiedzi w wątku z /u/testUser1 - Testowa wiadomość.');

        // Second thread
        $crawler = $client->request('GET', '/u/testUser1');
        $crawler = $client->click($crawler->filter('.kbin-entry-info')->selectLink('Wyślij wiadomość')->link());

        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'message[body]' => 'Testowa wiadomość2.',
                ]
            )
        );

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.kbin-profile-threads-page', '0 odpowiedzi w wątku z /u/testUser1 - Testowa wiadomość2.');

        $client->loginUser($this->getUserByUsername('testUser1'));

        $crawler = $client->request('GET', '/profil/wiadomosci');

        $this->assertSelectorTextContains('.kbin-profile-threads-page', '0 odpowiedzi w wątku z /u/testUser - Testowa wiadomość.');
        $this->assertSelectorTextContains('.kbin-profile-threads-page', '0 odpowiedzi w wątku z /u/testUser - Testowa wiadomość2.');
        $this->assertSelectorTextContains('.kbin-nav .bg-danger', '2');

        // Read message
        $crawler = $client->click($crawler->filter('.kbin-profile-threads-page')->selectLink('Testowa wiadomość.')->link());

        $this->assertSelectorTextContains('.kbin-nav .bg-danger', '1');

        // Reply
        $client->submit(
            $crawler->selectButton('Gotowe')->form(
                [
                    'message[body]' => 'Testowa odpowiedź.',
                ]
            )
        );

        $crawler = $client->request('GET', '/profil/wiadomosci');

        $this->assertSelectorTextContains('.kbin-profile-threads-page', '1 odpowiedzi w wątku z /u/testUser - Testowa wiadomość.');

        // Read reply
        $client->loginUser($this->getUserByUsername('testUser'));

        $crawler = $client->request('GET', '/profil/wiadomosci');

        $this->assertSelectorTextContains('.kbin-profile-threads-page', '1 odpowiedzi w wątku z /u/testUser1 - Testowa wiadomość.');
        $this->assertSelectorTextContains('.kbin-nav .bg-danger', '1');
    }
}
