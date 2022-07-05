<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\Tests\WebTestCase;

class PostVotersControllerTest extends WebTestCase
{
    public function testUserCanSeePostVoters(): void
    {
        $client = $this->createClient();

        $this->getMagazineByName('acme');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JaneDoe');

        $post = $this->createPost('post test', null, $user);

        $this->createVote(1, $post, $user1);
        $this->createVote(1, $post, $user2);

        $id      = $post->getId();
        $crawler = $client->request('GET', "/m/acme/w/$id/-/głosy");

        $this->assertCount(2, $crawler->filter('.kbin-voters .card'));
    }

    public function testXmlUserCanSeePostVoters(): void
    {
        $client = $this->createClient();

        $this->getMagazineByName('acme');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JaneDoe');

        $post = $this->createPost('post test', null, $user);

        $this->createVote(1, $post, $user1);
        $this->createVote(1, $post, $user2);

        $id = $post->getId();
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $client->request('GET', "/m/acme/w/$id/-/głosy");

        $this->assertStringContainsString('JaneDoe', $client->getResponse()->getContent());
    }
}
