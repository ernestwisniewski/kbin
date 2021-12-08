<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Service\UserManager;
use App\Tests\WebTestCase;

class PostFrontControllerTest extends WebTestCase
{
    public function testUserCanSeeFrontPosts()
    {
        $client = $this->createClient();

        $magazine = $this->getMagazineByName('polityka');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('regularUser');

        $this->createPost('post test', null, $user);
        $this->createPost('post test2', null, $user1);

        $crawler = $client->request('GET', "/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));
    }


    public function testUserCanSeeSubscribedMagazinePosts()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

        $magazine = $this->getMagazineByName('polityka', $user2);

        $magazineManager = static::getContainer()->get(MagazineManager::class);
        $magazineManager->subscribe($magazine, $user);

        $this->createPost('post test', null, $user);
        $this->createPost('post test2', null, $user2);
        $this->createPost('post test3', null, $user3);

        $crawler = $client->request('GET', "/sub/wpisy");

        $this->assertCount(3, $crawler->filter('.kbin-post'));
    }

    public function testUserCanSeeSubscribedUserPosts()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

        $magazine = $this->getMagazineByName('polityka', $user2);

        $userManager = static::getContainer()->get(UserManager::class);
        $userManager->follow($user, $user3);

        $this->createPost('post test', null, $user);
        $this->createPost('post test2', null, $user2);
        $this->createPost('post test3', null, $user3);

        $crawler = $client->request('GET', "/sub/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));
    }

    public function testUserCanSeeModeratedPosts()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user2 = $this->getUserByUsername('regularUser2');
        $user3 = $this->getUserByUsername('regularUser3');

        $magazine1 = $this->getMagazineByName('polityka', $user);
        $magazine2 = $this->getMagazineByName('polityka2', $user2);
        $magazine3 = $this->getMagazineByName('polityka3', $user2);

        $magazineManager    = static::getContainer()->get(MagazineManager::class);
        $moderatorDto       = new ModeratorDto($magazine2);
        $moderatorDto->user = $user;
        $magazineManager->addModerator($moderatorDto);

        $this->createPost('post test', $magazine1, $user2);
        $this->createPost('post test2', $magazine2, $user3);
        $this->createPost('post test2', $magazine3, $user3);

        $crawler = $client->request('GET', "/mod/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));
    }
}
