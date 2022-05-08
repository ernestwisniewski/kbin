<?php declare(strict_types=1);

namespace App\Tests\Controller\Post;

use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Tests\WebTestCase;

class PostDeleteControllerTest extends WebTestCase
{
    public function testAuthorCanDeletePost()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $post = $this->createPost('post test', null, $user1);

        $comment1 = $this->createPostComment('test', $post, $user);
        $comment2 = $this->createPostComment('test2', $post, $user1);
        $comment3 = $this->createPostComment('test3', $post, $user2);

        $this->createVote(1, $post, $user2);
        $this->createVote(1, $comment1, $user2);
        $this->createVote(1, $comment2, $user2);
        $this->createVote(1, $comment3, $user);

        $crawler = $client->request('GET', "/m/polityka/wpisy");

        $this->assertCount(1, $crawler->filter('.kbin-post'));

        $client->submit(
            $crawler->selectButton('usuń')->form()
        );

        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();

        $this->assertCount(0, $crawler->filter('.kbin-post'));

        $repository = static::getContainer()->get(PostRepository::class);
        $this->assertSame(1, $repository->count([]));

        $repository = static::getContainer()->get(PostCommentRepository::class);
        $this->assertSame(3, $repository->count([]));
    }

    public function testAdminCanPurgePost()
    {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $post = $this->createPost('post test', null, $user1);

        $comment1 = $this->createPostComment('test', $post, $user);
        $comment2 = $this->createPostComment('test2', $post, $user1);
        $comment3 = $this->createPostComment('test3', $post, $user2);

        $this->createVote(1, $post, $user2);
        $this->createVote(1, $comment1, $user2);
        $this->createVote(1, $comment2, $user2);
        $this->createVote(1, $comment3, $user);

        $client->loginUser($admin = $this->getUserByUsername('admin', true));

        $crawler = $client->request('GET', "/m/polityka/w/{$post->getId()}");

        $client->submit($crawler->filter('.kbin-post-main')->selectButton('wyczyść')->form());

        $crawler = $client->followRedirect();

        $repository = static::getContainer()->get(PostRepository::class);
        $this->assertSame(0, $repository->count([]));

        $repository = static::getContainer()->get(PostCommentRepository::class);
        $this->assertSame(0, $repository->count([]));
    }

    public function testModeratorCanRestorePost() {
        $client = $this->createClient();
        $client->loginUser($user = $this->getUserByUsername('regularUser'));

        $user1 = $this->getUserByUsername('regularUser');
        $user2 = $this->getUserByUsername('regularUser2');

        $post = $this->createPost('post test', null, $user2);
        $post = $this->createPost('post test2', null, $user2);

        $crawler = $client->request('GET', "/m/polityka/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));

        $client->submit(
            $crawler->selectButton('usuń')->form()
        );

        $crawler = $client->followRedirect();

        $this->assertCount(1, $crawler->filter('.kbin-post'));

        $crawler = $client->click($crawler->filter('.kbin-sidebar')->selectLink('Kosz')->link());

        $this->assertCount(1, $crawler->filter('.kbin-post'));

        $client->submit(
            $crawler->selectButton('przywróć')->form()
        );

        $crawler = $client->request('GET', "/m/polityka/wpisy");

        $this->assertCount(2, $crawler->filter('.kbin-post'));
    }
}
