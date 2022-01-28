<?php declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Tests\WebTestCase;

class UserFrontControllerTest extends WebTestCase
{
    public function testCanShowPublicProfile()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('testUser'));

        $this->getEntryByTitle('treść1');
        $entry = $this->getEntryByTitle('treść2');
        $this->createEntryComment('createEntryComment', $entry);
        $post = $this->createPost('createPost');
        $this->createPostComment('createPostComment', $post);
        
        $crawler = $client->request('GET', '/u/regularUser');

        $this->assertCount(5, $crawler->filter('.kbin-user-front-page')->children());
    }
}
