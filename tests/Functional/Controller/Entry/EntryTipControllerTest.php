<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Entry;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntryTipControllerTest extends WebTestCase
{
    public function testUserCanShowTipForms(): void // @todo
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user2->cardanoWalletAddress = 'wallet_acme'; // @todo

        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $magazine = $this->getMagazineByName('acme', $user2);

        $entry = $this->getEntryByTitle('treść 2', null, 'test', $magazine, $user2);
        $entry->isOc = true;

        $manager->flush();

        $crawler = $client->request('GET', "/m/acme/t/{$entry->getId()}/-/tipy");

        $this->assertStringContainsString('Czym są i jak działają tipy za wartościowe treści?', $crawler->filter('.kbin-tips-page')->html());
    }

    public function testXmlUserCanShowTipForms(): void // @todo
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user2->cardanoWalletAddress = 'wallet_acme';

        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $magazine = $this->getMagazineByName('acme', $user2);

        $entry = $this->getEntryByTitle('treść 2', null, 'test', $magazine, $user2);
        $entry->isOc = true;

        $manager->flush();

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', "/m/acme/t/{$entry->getId()}/-/tipy");

        $this->assertStringContainsString('Czym jest Karabin Cardano Stake Pool?', $client->getResponse()->getContent());
    }
}
