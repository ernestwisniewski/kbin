<?php declare(strict_types=1);

namespace App\Tests;

use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\DTO\MagazineBanDto;
use App\DTO\MagazineDto;
use App\DTO\PostCommentDto;
use App\DTO\PostDto;
use App\Entity\Contracts\VoteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\Vote;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Service\VoteManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected ArrayCollection $users;
    protected ArrayCollection $magazines;
    protected ArrayCollection $entries;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->users     = new ArrayCollection();
        $this->magazines = new ArrayCollection();
        $this->entries   = new ArrayCollection();
    }

    public function createEntryComment(string $body, ?Entry $entry = null, ?User $user = null, ?EntryComment $parent = null): EntryComment
    {
        /**
         * @var $manager EntryCommentManager
         */
        $manager = static::getContainer()->get(EntryCommentManager::class);

        if ($parent) {
            $dto = (new EntryCommentDto())->createWithParent($entry ?? $this->getEntryByTitle('Przykladowa treść'), $parent, null, $body);
        } else {
            $dto        = new EntryCommentDto();
            $dto->entry = $entry ?? $this->getEntryByTitle('Przykladowa treść');
            $dto->body  = $body;
        }

        return $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));
    }

    protected function getEntryByTitle(
        string $title,
        ?string $url = null,
        ?string $body = null,
        ?Magazine $magazine = null,
        ?User $user = null
    ): Entry {
        $entry = $this->entries->filter(
            static function (Entry $entry) use ($title) {
                return $entry->title === $title;
            }
        )->first();

        if (!$entry) {
            $magazine = $magazine ?? $this->getMagazineByName('polityka');
            $user     = $user ?? $this->getUserByUsername('regularUser');
            $entry    = $this->createEntry($title, $magazine, $user, $url, $body);
        }

        return $entry;
    }

    protected function getMagazineByName(string $name, ?User $user = null): Magazine
    {
        $magazine = $this->magazines->filter(
            static function (Magazine $magazine) use ($name) {
                return $magazine->name === $name;
            }
        )->first();

        return $magazine ?: $this->createMagazine($name, null, $user);
    }

    protected function createEntry(string $title, Magazine $magazine, User $user, ?string $url = null, ?string $body = 'testowa treść'): Entry
    {
        /**
         * @var $manager EntryManager
         */
        $manager = static::getContainer()->get(EntryManager::class);

        $dto           = new EntryDto();
        $dto->magazine = $magazine;
        $dto->title    = $title;
        $dto->user     = $user;
        $dto->url      = $url;
        $dto->body     = $body;

        $entry = $manager->create($dto, $user);

        $this->entries->add($entry);

        return $entry;
    }

    public function createVote(int $choice, VoteInterface $subject, User $user): Vote
    {
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        /**
         * @var $voteManager VoteManager
         */
        $voteManager = static::getContainer()->get(VoteManager::class);

        $vote = $voteManager->vote($choice, $subject, $user);

        $manager->persist($vote);
        $manager->flush();

        return $vote;
    }

    public function createPost(string $body, ?Magazine $magazine = null, ?User $user = null): Post
    {
        /**
         * @var $manager PostManager
         */
        $manager = static::getContainer()->get(PostManager::class);

        $dto           = new PostDto();
        $dto->magazine = $magazine ?: $this->getMagazineByName('polityka');
        $dto->body     = $body;

        return $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));
    }

    public function createPostComment(string $body, Post $post, ?User $user = null): PostComment
    {
        /**
         * @var $manager PostCommentManager
         */
        $manager = static::getContainer()->get(PostCommentManager::class);

        $dto       = new PostCommentDto();
        $dto->post = $post;
        $dto->body = $body;

        return $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));
    }

    protected function loadExampleMagazines(): void
    {
        $this->loadExampleUsers();

        foreach ($this->provideMagazines() as $data) {
            $this->createMagazine($data['name'], $data['title'], $data['user']);
        }
    }

    protected function loadExampleUsers(): void
    {
        foreach ($this->provideUsers() as $data) {
            $this->createUser($data['username'], $data['email'], $data['password']);
        }
    }

    private function provideUsers(): iterable
    {
        yield [
            'username' => 'adminUser',
            'password' => 'adminUser123',
            'email'    => 'adminUser@example.com',
        ];

        yield [
            'username' => 'regularUser',
            'password' => 'regularUser123',
            'email'    => 'regularUser@example.com',
        ];
    }

    private function createUser(string $username, string $email = null, string $password = null, $active = true): User
    {
        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $user = new User($email ? $email : $username.'@example.com', $username, $password ? $password : 'secret');

        $user->isVerified                   = $active;
        $user->notifyOnNewEntry             = true;
        $user->notifyOnNewEntryReply        = true;
        $user->notifyOnNewEntryCommentReply = true;
        $user->notifyOnNewPost              = true;
        $user->notifyOnNewPostReply         = true;
        $user->notifyOnNewPostCommentReply  = true;
        $user->showProfileFollowings        = true;
        $user->showProfileSubscriptions     = true;

        $manager->persist($user);
        $manager->flush();

        $this->users->add($user);

        return $user;
    }

    private function provideMagazines(): iterable
    {
        yield [
            'name'  => 'polityka',
            'title' => 'Magazyn polityczny',
            'user'  => $this->getUserByUsername('regularUser'),
        ];

        yield [
            'name'  => 'kbin',
            'title' => 'kbin devlog',
            'user'  => $this->getUserByUsername('adminUser'),
        ];
    }

    protected function getUserByUsername(string $username, bool $isAdmin = false): User
    {
        $user = $this->users->filter(
            static function (User $user) use ($username) {
                return $user->getUsername() === $username;
            }
        )->first();

        $user = $user ?: $this->createUser($username);

        if ($isAdmin) {
            $user->roles = ['ROLE_ADMIN'];
            $manager     = static::getContainer()->get(EntityManagerInterface::class);

            $manager->persist($user);
            $manager->flush();
        }

        return $user;
    }

    private function createMagazine(string $name, string $title = null, User $user = null): Magazine
    {
        /**
         * @var $manager MagazineManager
         */
        $manager = static::getContainer()->get(MagazineManager::class);

        $dto        = new MagazineDto();
        $dto->name  = $name;
        $dto->title = $title ?? 'Przykładowy magazyn';

        $magazine = $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));

        $this->magazines->add($magazine);

        return $magazine;
    }

    protected function loadNotificationsFixture()
    {
        $owner    = $this->getUserByUsername('owner');
        $magazine = $this->getMagazineByName('polityka', $owner);

        $actor = $this->getUserByUsername('actor');

        $entry   = $this->getEntryByTitle('test', null, 'test', $magazine, $actor);
        $comment = $this->createEntryComment('test', $entry, $actor);
        (static::getContainer()->get(EntryCommentManager::class))->delete($owner, $comment);
        (static::getContainer()->get(EntryManager::class))->delete($owner, $entry);

        $post    = $this->createPost('test', $magazine, $actor);
        $comment = $this->createPostComment('test', $post, $actor);
        (static::getContainer()->get(PostCommentManager::class))->delete($owner, $comment);
        (static::getContainer()->get(PostManager::class))->delete($owner, $post);

        (static::getContainer()->get(MagazineManager::class))->ban(
            $magazine,
            $actor,
            $owner,
            (new MagazineBanDto())->create('test', new \DateTime('+1 day'))
        );
    }
}
