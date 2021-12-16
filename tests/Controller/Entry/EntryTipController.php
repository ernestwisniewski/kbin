<?php declare(strict_types=1);

namespace App\Tests\Controller\Entry;

use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntryTipController extends WebTestCase
{
    public function testXmlUserCanShowTipForms() // @todo
    {
        $client  = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2    = $this->getUserByUsername('regularUser2');
        $user2->cardanoWalletAddress('abc');

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $manager->persist($user2);
        $manager->flush();

        $magazine = $this->getMagazineByName('polityka', $user2);

        $entry = $this->getEntryByTitle('treść 2', null, null, $magazine, $user2);

        $id    = $entry->getId();

        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', "/m/polityka/t/$id/-/tipy");

        $this->assertStringContainsString('Czym jest Karabin Cardano Stake Pool?', $client->getResponse()->getContent());
    }
}
