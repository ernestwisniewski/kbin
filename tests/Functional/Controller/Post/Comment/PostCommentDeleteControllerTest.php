<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Post\Comment;

use App\Tests\WebTestCase;

class PostCommentDeleteControllerTest extends WebTestCase
{
    public function testUserCanDeletePostComment()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $post = $this->createPost('deletion test', magazine: $magazine, user: $user);
        $comment = $this->createPostComment('delete me!', $post, $user);
        $client->loginUser($user);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/deletion-test");

        $this->assertSelectorExists('#comments form[action$="delete"]');
        $client->submit(
            $crawler->filter('#comments form[action$="delete"]')->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
    }

    public function testUserCanSoftDeletePostComment()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $magazine = $this->getMagazineByName('acme');
        $post = $this->createPost('deletion test', magazine: $magazine, user: $user);
        $comment = $this->createPostComment('delete me!', $post, $user);
        $reply = $this->createPostCommentReply('Are you deleted yet?', $post, $user, $comment);
        $client->loginUser($user);

        $crawler = $client->request('GET', "/m/acme/p/{$post->getId()}/deletion-test");

        $this->assertSelectorExists("#post-comment-{$comment->getId()} form[action$=\"delete\"]");
        $client->submit(
            $crawler->filter("#post-comment-{$comment->getId()} form[action$=\"delete\"]")->selectButton('delete')->form()
        );

        $this->assertResponseRedirects();
        $client->request('GET', "/m/acme/p/{$post->getId()}/deletion-test");

        $this->assertSelectorTextContains("#post-comment-{$comment->getId()} .content", 'deleted_by_author');
    }
}
