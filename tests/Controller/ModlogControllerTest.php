<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\DTO\MagazineBanDto;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Tests\WebTestCase;

class ModlogControllerTest extends WebTestCase
{
    public function testModlog()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        $owner    = $this->getUserByUsername('owner');
        $magazine = $this->getMagazineByName('polityka', $owner);

        $actor = $this->getUserByUsername('actor');

        $entry   = $this->getEntryByTitle('test', null, 'test', $magazine, $actor);
        $comment = $this->createEntryComment('test', $entry, $actor);
        (self::$container->get(EntryManager::class))->delete($owner, $entry);
        (self::$container->get(EntryCommentManager::class))->delete($owner, $comment);

        $post    = $this->createPost('test', $magazine, $actor);
        $comment = $this->createPostComment('test', $post, $actor);
        (self::$container->get(PostManager::class))->delete($owner, $post);
        (self::$container->get(PostCommentManager::class))->delete($owner, $comment);

        (self::$container->get(MagazineManager::class))->ban($magazine, $actor, $owner, (new MagazineBanDto())->create('test', new \DateTime('+1 day')));

        $crawler = $client->request('GET', '/modlog');

        $this->assertCount(5, $crawler->filter('.table-responsive tr'));
    }
}
