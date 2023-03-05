<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Service\MagazineManager;
use App\Service\UserManager;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class UserFrontControllerTest extends WebTestCase
{
    public function testOverview(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/u/JohnDoe');

        $this->assertSelectorTextContains('.options.options--top .active', 'overview');
        $this->assertEquals(2, $crawler->filter('#main .entry')->count());
        $this->assertEquals(2, $crawler->filter('#main .entry-comment')->count());
        $this->assertEquals(2, $crawler->filter('#main .post')->count());
        $this->assertEquals(2, $crawler->filter('#main .post-comment')->count());
    }

    public function testThreadsPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/u/JohnDoe');
        $crawler = $client->click($crawler->filter('#main .options')->selectLink('threads')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'threads (1)');
        $this->assertEquals(1, $crawler->filter('#main .entry')->count());
    }

    public function testCommentsPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/u/JohnDoe');
        $client->click($crawler->filter('#main .options')->selectLink('comments')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'comments (2)');
        $this->assertEquals(2, $crawler->filter('#main .entry-comment')->count());
    }

    public function testPostsPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/u/JohnDoe');
        $crawler = $client->click($crawler->filter('#main .options')->selectLink('posts')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'posts (1)');
        $this->assertEquals(1, $crawler->filter('#main .post')->count());
    }

    public function testRepliesPage(): void
    {
        $client = $this->prepareEntries();

        $crawler = $client->request('GET', '/u/JohnDoe');
        $crawler = $client->click($crawler->filter('#main .options')->selectLink('replies')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'replies (2)');
        $this->assertEquals(2, $crawler->filter('#main .post-comment')->count());
        $this->assertEquals(2, $crawler->filter('#main .post')->count());
    }

    public function createSubscriptionsPage()
    {
        $client = $this->createClient();

        $user = $this->getUserByUsername('JohnDoe');
        $this->getMagazineByName('kbin');
        $this->getMagazineByName('mag', $this->getUserByUsername('JaneDoe'));

        $manager = $this->getContainer()->get(MagazineManager::class);
        $manager->subscribe($this->getMagazineByName('mag'), $user);

        $client->loginUser($user);

        $crawler = $client->request('GET', '/u/JohnDoe');
        $crawler = $client->click($crawler->filter('#main .options')->selectLink('subscriptions')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'subscriptions (2)');
        $this->assertEquals(2, $crawler->filter('#main .magazines ul li')->count());
    }

    public function testFollowersPage(): void
    {
        $client = $this->createClient();

        $user1 = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JaneDoe');

        $manager = $this->getContainer()->get(UserManager::class);
        $manager->follow($user2, $user1);

        $client->loginUser($user1);

        $crawler = $client->request('GET', '/u/JohnDoe');
        $crawler = $client->click($crawler->filter('#main .options')->selectLink('followers')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'followers (1)');
        $this->assertEquals(1, $crawler->filter('#main .users ul li')->count());
    }

    public function testFollowingPage(): void
    {
        $client = $this->createClient();

        $user1 = $this->getUserByUsername('JohnDoe');
        $user2 = $this->getUserByUsername('JaneDoe');

        $manager = $this->getContainer()->get(UserManager::class);
        $manager->follow($user1, $user2);

        $client->loginUser($user1);

        $crawler = $client->request('GET', '/u/JohnDoe');
        $crawler = $client->click($crawler->filter('#main .options')->selectLink('following')->link());

        $this->assertSelectorTextContains('.options.options--top .active', 'following (1)');
        $this->assertEquals(1, $crawler->filter('#main .users ul li')->count());
    }

    private function prepareEntries(): KernelBrowser
    {
        $client = $this->createClient();

        $entry1 = $this->getEntryByTitle(
            'test entry 1',
            'https://kbin.pub',
            null,
            $this->getMagazineByName('mag', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );
        $entry2 = $this->getEntryByTitle(
            'test entry 2',
            'https://kbin.pub',
            null,
            $this->getMagazineByName('mag', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );
        $entry3 = $this->getEntryByTitle('test entry 3');

        $this->createEntryComment('test entry comment 1', $entry1);
        $this->createEntryComment('test entry comment 2', $entry2, $this->getUserByUsername('JaneDoe'));
        $this->createEntryComment('test entry comment 3', $entry3);

        $post1 = $this->createPost(
            'test post 1',
            $this->getMagazineByName('mag', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );
        $post2 = $this->createPost(
            'test post 2',
            $this->getMagazineByName('mag', $this->getUserByUsername('JaneDoe')),
            $this->getUserByUsername('JaneDoe')
        );
        $post3 = $this->createPost('test post 3');

        $this->createPostComment('test post comment 1', $post1);
        $this->createPostComment('test post comment 2', $post2, $this->getUserByUsername('JaneDoe'));
        $this->createPostComment('test post comment 3', $post3);

        return $client;
    }
}
