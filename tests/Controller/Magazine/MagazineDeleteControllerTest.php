<?php declare(strict_types=1);

namespace App\Tests\Controller\Magazine;

use App\Repository\MagazineRepository;
use App\Tests\WebTestCase;

class MagazineDeleteControllerTest extends WebTestCase
{
    public function testUserCanDeleteMagazine()
    {
        $client = $this->createClient();

        $client->loginUser($owner = $this->getUserByUsername('regularUser'));

        $actor = $this->getUserByUsername('actor');

        $this->createEntry('test', $this->getMagazineByName('polityka'), $owner);
        $entry = $this->createEntry('test', $this->getMagazineByName('polityka'), $actor);

        $this->createEntryComment('test', $entry, $owner);

        $this->createPost('test', $this->getMagazineByName('polityka'), $owner);
        $post = $this->createPost('test', $this->getMagazineByName('polityka'), $actor);

        $this->createPostComment('test', $post, $owner);

        $crawler = $client->request('GET', '/m/polityka');

        $crawler = $client->click($crawler->filter('.kbin-sidebar .kbin-magazine-panel .kbin-quick-links')->selectLink('Ogólne')->link());

        $client->submit(
            $crawler->filter('.kbin-magazine-edit')->selectButton('usuń')->form()
        );

        $client->followRedirect();

        $client->request('GET', '/m/polityka');

        $this->assertResponseStatusCodeSame(404);

        $repo = $this->getContainer()->get(MagazineRepository::class);

        $this->assertSame(1, $repo->count([]));
    }
}
