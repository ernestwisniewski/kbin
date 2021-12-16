<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Service\UserManager;
use App\Tests\WebTestCase;

class PostVotersControllerTest extends WebTestCase
{
    public function testUserCanSeePostVoters()
    {
        $client = $this->createClient();

        $this->getMagazineByName('polityka');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $post = $this->createPost('post test', null, $user);

        $this->createVote(1, $post, $user1);
        $this->createVote(1, $post, $user2);

        $id = $post->getId();
        $crawler = $client->request('GET', "/m/polityka/w/$id/-/głosy");

        $this->assertCount(2, $crawler->filter('.kbin-voters .card'));
    }

    public function testXmlUserCanSeePostVoters()
    {
        $client = $this->createClient();

        $this->getMagazineByName('polityka');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $post = $this->createPost('post test', null, $user);

        $this->createVote(1, $post, $user1);
        $this->createVote(1, $post, $user2);

        $id = $post->getId();
        $client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $crawler = $client->request('GET', "/m/polityka/w/$id/-/głosy");

        $this->assertStringContainsString('regularUser2', $client->getResponse()->getContent());
    }
}
