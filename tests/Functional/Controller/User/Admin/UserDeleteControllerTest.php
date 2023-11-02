<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Admin;

use App\Tests\WebTestCase;

class UserDeleteControllerTest extends WebTestCase
{
    public function testAdminCanDeleteUser()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('An entry', body: 'test', user: $user);
        $entryComment = $this->createEntryComment('A comment', $entry, $user);
        $post = $this->createPost('A post', user: $user);
        $postComment = $this->createPostComment('A comment', $post, $user);
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/u/user');

        $this->assertSelectorExists('#sidebar .panel form[action$="delete_account"]');
        $client->submit(
            $crawler->filter('#sidebar .panel form[action$="delete_account"]')->selectButton('Delete account')->form()
        );

        $this->assertResponseRedirects();
    }

    public function testAdminCanPurgeUser()
    {
        $client = $this->createClient();
        $user = $this->getUserByUsername('user');
        $entry = $this->getEntryByTitle('An entry', body: 'test', user: $user);
        $entryComment = $this->createEntryComment('A comment', $entry, $user);
        $post = $this->createPost('A post', user: $user);
        $postComment = $this->createPostComment('A comment', $post, $user);
        $admin = $this->getUserByUsername('admin', isAdmin: true);
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/u/user');

        $this->assertSelectorExists('#sidebar .panel form[action$="purge_account"]');
        $client->submit(
            $crawler->filter('#sidebar .panel form[action$="purge_account"]')->selectButton('Purge account')->form()
        );

        $this->assertResponseRedirects();
    }
}
