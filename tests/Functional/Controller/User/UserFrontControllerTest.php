<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class UserFrontControllerTest extends WebTestCase
{
    public function testOverview(): void
    {
        $client = $this->prepareEntries();

        $cralwer = $client->request('GET', '/u/JohnDoe');

        $this->assertSelectorTextContains('.options.options--top', 'overview');

        $this->assertEquals(2, $cralwer->filter('#main .entry'));
        $this->assertEquals(2, $cralwer->filter('#main .entry-comment'));
        $this->assertEquals(2, $cralwer->filter('#main .post'));
        $this->assertEquals(2, $cralwer->filter('#main .post-comment'));
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
