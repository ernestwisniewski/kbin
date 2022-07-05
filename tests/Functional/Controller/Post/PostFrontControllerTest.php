<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post;

use App\DTO\ModeratorDto;
use App\Service\MagazineManager;
use App\Service\UserManager;
use App\Tests\WebTestCase;

class PostFrontControllerTest extends WebTestCase
{
    public function testUserCanSeeFrontPosts(): void
    {
        $client = $this->createClient();

        $magazine = $this->getMagazineByName('acme');

        $user  = $this->getUserByUsername('user');
        $user1 = $this->getUserByUsername('JohnDoe');

        $this->createPost('post test', null, $user);
        $this->createPost('post test2', null, $user1);

        $crawler = $client->request('GET', "/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));
    }


    public function testUserCanSeeSubscribedMagazinePosts(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');

        $magazine = $this->getMagazineByName('acme', $user2);

        $magazineManager = static::getContainer()->get(MagazineManager::class);
        $magazineManager->subscribe($magazine, $user);

        $this->createPost('post test', null, $user);
        $this->createPost('post test2', null, $user2);
        $this->createPost('post test3', null, $user3);

        $crawler = $client->request('GET', "/sub/wpisy");

        $this->assertCount(3, $crawler->filter('.kbin-post'));
    }

    public function testUserCanSeeSubscribedUserPosts(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');

        $this->getMagazineByName('acme', $user2);

        $userManager = static::getContainer()->get(UserManager::class);
        $userManager->follow($user, $user3);

        $this->createPost('post test', null, $user);
        $this->createPost('post test2', null, $user2);
        $this->createPost('post test3', null, $user3);

        $crawler = $client->request('GET', "/sub/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));
    }

    public function testUserCanSeeModeratedPosts(): void
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('JohnDoe'));

        $user2 = $this->getUserByUsername('JaneDoe');
        $user3 = $this->getUserByUsername('MaryJane');

        $magazine1 = $this->getMagazineByName('acme', $user);
        $magazine2 = $this->getMagazineByName('acme2', $user2);
        $magazine3 = $this->getMagazineByName('acme3', $user2);

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
