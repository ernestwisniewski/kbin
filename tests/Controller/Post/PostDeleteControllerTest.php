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
        $posts    = $repository->findAll();
        $this->assertCount(1, $posts);

        $repository = static::getContainer()->get(PostCommentRepository::class);
        $comments   = $repository->findAll();
        $this->assertCount(3, $comments);
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
        $posts    = $repository->findAll();
        $this->assertCount(0, $posts);

        $repository = static::getContainer()->get(PostCommentRepository::class);
        $comments   = $repository->findAll();
        $this->assertCount(0, $comments);
    }
}
