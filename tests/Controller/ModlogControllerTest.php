<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Tests\WebTestCase;

class ModlogControllerTest extends WebTestCase
{

    public function modlog()
    {
        $client = $this->createClient();
        $client->loginUser($this->getUserByUsername('owner'));

        $owner    = $this->getUserByUsername('actor');
        $magazine = $this->getMagazineByName('polityka', $owner);

        $actor = $this->getUserByUsername('actor');

        $entry   = $this->getEntryByTitle('test', null, 'test', $magazine, $actor);
        $comment = $this->createEntryComment('test', $entry, $actor);
        (self::$container->get(EntryCommentManager::class))->delete($comment);
        (self::$container->get(EntryManager::class))->delete($entry);

        $post    = $this->createPost('test', $magazine, $actor);
        $comment = $this->createPostComment('test', $post, $actor);
        (self::$container->get(PostManager::class))->delete($post);
        (self::$container->get(PostCommentManager::class))->delete($comment);

        (self::$container->get(MagazineManager::class))->ban($magazine, $actor);
    }
}
