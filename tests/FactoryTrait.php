<?php

namespace App\Tests;

use App\DTO\EntryCommentDto;
use App\DTO\EntryDto;
use App\DTO\MagazineBanDto;
use App\DTO\MagazineDto;
use App\DTO\PostCommentDto;
use App\DTO\PostDto;
use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\FavouriteManager;
use App\Service\MagazineManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use App\Service\VoteManager;
use Doctrine\ORM\EntityManagerInterface;

trait FactoryTrait
{
    public function createVote(int $choice, VotableInterface $subject, User $user): void
    {
        if (VotableInterface::VOTE_UP === $choice) {
            $favManager = $this->getService(FavouriteManager::class);
            $favManager->toggle($user, $subject);
        } else {
            $voteManager = $this->getService(VoteManager::class);
            $voteManager->vote($choice, $subject, $user);
        }
    }

    protected function loadExampleMagazines(): void
    {
        $this->loadExampleUsers();

        foreach ($this->provideMagazines() as $data) {
            $this->createMagazine($data['name'], $data['title'], $data['user'], $data['isAdult'], $data['description']);
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
            'email' => 'adminUser@example.com',
        ];

        yield [
            'username' => 'JohnDoe',
            'password' => 'JohnDoe123',
            'email' => 'JohnDoe@example.com',
        ];
    }

    private function createUser(string $username, string $email = null, string $password = null, $active = true, $hideAdult = true): User
    {
        $manager = $this->getService(EntityManagerInterface::class);

        $user = new User($email ?: $username.'@example.com', $username, $password ?: 'secret');

        $user->isVerified = $active;
        $user->notifyOnNewEntry = true;
        $user->notifyOnNewEntryReply = true;
        $user->notifyOnNewEntryCommentReply = true;
        $user->notifyOnNewPost = true;
        $user->notifyOnNewPostReply = true;
        $user->notifyOnNewPostCommentReply = true;
        $user->showProfileFollowings = true;
        $user->showProfileSubscriptions = true;
        $user->hideAdult = $hideAdult;
        $user->avatar = $this->createImage(bin2hex(random_bytes(20)).'.png');

        $manager->persist($user);
        $manager->flush();

        $this->users->add($user);

        return $user;
    }

    private function provideMagazines(): iterable
    {
        yield [
            'name' => 'acme',
            'title' => 'Magazyn polityczny',
            'user' => $this->getUserByUsername('JohnDoe'),
            'isAdult' => false,
            'description' => 'Foobar',
        ];

        yield [
            'name' => 'kbin',
            'title' => 'kbin devlog',
            'user' => $this->getUserByUsername('adminUser'),
            'isAdult' => false,
            'description' => 'development process in detail',
        ];

        yield [
            'name' => 'adult',
            'title' => 'Adult only',
            'user' => $this->getUserByUsername('JohnDoe'),
            'isAdult' => true,
            'description' => 'Not for kids',
        ];

        yield [
            'name' => 'starwarsmemes@republic.new',
            'title' => 'starwarsmemes@republic.new',
            'user' => $this->getUserByUsername('adminUser'),
            'isAdult' => false,
            'description' => "It's a trap",
        ];
    }

    protected function getUserByUsername(string $username, bool $isAdmin = false, bool $hideAdult = true): User
    {
        $user = $this->users->filter(
            static function (User $user) use ($username) {
                return $user->getUsername() === $username;
            }
        )->first();

        $user = $user ?: $this->createUser($username, hideAdult: $hideAdult);

        if ($isAdmin) {
            $user->roles = ['ROLE_ADMIN'];
            $manager = $this->getService(EntityManagerInterface::class);

            $manager->persist($user);
            $manager->flush();
        }

        return $user;
    }

    protected function setAdmin(User $user): void
    {
        $user->roles = ['ROLE_ADMIN'];
        $manager = $this->getService(EntityManagerInterface::class);

        $manager->persist($user);
        $manager->flush();

        $manager->refresh($user);
    }

    private function createMagazine(
        string $name,
        string $title = null,
        User $user = null,
        bool $isAdult = false,
        string $description = null
    ): Magazine {
        $manager = $this->getService(MagazineManager::class);

        $dto = new MagazineDto();
        $dto->name = $name;
        $dto->title = $title ?? 'Magazine title';
        $dto->isAdult = $isAdult;
        $dto->description = $description;

        if (str_contains($name, '@')) {
            [$name, $host] = explode('@', $name);
            $dto->apId = $name;
            $dto->apProfileId = "https://{$host}/m/{$name}";
        }

        $magazine = $manager->create($dto, $user ?? $this->getUserByUsername('JohnDoe'));

        $this->magazines->add($magazine);

        return $magazine;
    }

    protected function loadNotificationsFixture()
    {
        $owner = $this->getUserByUsername('owner');
        $magazine = $this->getMagazineByName('acme', $owner);

        $actor = $this->getUserByUsername('actor');
        $regular = $this->getUserByUsername('JohnDoe');

        $entry = $this->getEntryByTitle('test', null, 'test', $magazine, $actor);
        $comment = $this->createEntryComment('test', $entry, $regular);
        $this->getService(EntryCommentManager::class)->delete($owner, $comment);
        $this->getService(EntryManager::class)->delete($owner, $entry);

        $post = $this->createPost('test', $magazine, $actor);
        $comment = $this->createPostComment('test', $post, $regular);
        $this->getService(PostCommentManager::class)->delete($owner, $comment);
        $this->getService(PostManager::class)->delete($owner, $post);

        $this->getService(MagazineManager::class)->ban(
            $magazine,
            $actor,
            $owner,
            (new MagazineBanDto())->create('test', new \DateTime('+1 day'))
        );
    }

    protected function getMagazineByName(string $name, User $user = null, bool $isAdult = false): Magazine
    {
        $magazine = $this->magazines->filter(
            static function (Magazine $magazine) use ($name) {
                return $magazine->name === $name;
            }
        )->first();

        return $magazine ?: $this->createMagazine($name, null, $user, $isAdult);
    }

    protected function getEntryByTitle(
        string $title,
        string $url = null,
        string $body = null,
        Magazine $magazine = null,
        User $user = null
    ): Entry {
        $entry = $this->entries->filter(
            static function (Entry $entry) use ($title) {
                return $entry->title === $title;
            }
        )->first();

        if (!$entry) {
            $magazine = $magazine ?? $this->getMagazineByName('acme');
            $user = $user ?? $this->getUserByUsername('JohnDoe');
            $entry = $this->createEntry($title, $magazine, $user, $url, $body);
        }

        return $entry;
    }

    protected function createEntry(
        string $title,
        Magazine $magazine,
        User $user,
        string $url = null,
        ?string $body = 'Test entry content'
    ): Entry {
        $manager = $this->getService(EntryManager::class);

        $dto = new EntryDto();
        $dto->magazine = $magazine;
        $dto->title = $title;
        $dto->user = $user;
        $dto->url = $url;
        $dto->body = $body;
        $dto->lang = 'en';

        $entry = $manager->create($dto, $user);

        $this->entries->add($entry);

        return $entry;
    }

    public function createEntryComment(
        string $body,
        Entry $entry = null,
        User $user = null,
        EntryComment $parent = null
    ): EntryComment {
        $manager = $this->getService(EntryCommentManager::class);

        if ($parent) {
            $dto = (new EntryCommentDto())->createWithParent(
                $entry ?? $this->getEntryByTitle('test entry content', 'https://kbin.pub'),
                $parent,
                null,
                $body
            );
        } else {
            $dto = new EntryCommentDto();
            $dto->entry = $entry ?? $this->getEntryByTitle('test entry content', 'https://kbin.pub');
            $dto->body = $body;
            $dto->lang = 'en';
        }

        return $manager->create($dto, $user ?? $this->getUserByUsername('JohnDoe'));
    }

    public function createPost(string $body, Magazine $magazine = null, User $user = null): Post
    {
        $manager = $this->getService(PostManager::class);
        $dto = new PostDto();
        $dto->magazine = $magazine ?: $this->getMagazineByName('acme');
        $dto->body = $body;
        $dto->lang = 'en';

        return $manager->create($dto, $user ?? $this->getUserByUsername('JohnDoe'));
    }

    public function createPostComment(string $body, Post $post = null, User $user = null): PostComment
    {
        $manager = $this->getService(PostCommentManager::class);

        $dto = new PostCommentDto();
        $dto->post = $post ?? $this->createPost('test post content');
        $dto->body = $body;
        $dto->lang = 'en';

        return $manager->create($dto, $user ?? $this->getUserByUsername('JohnDoe'));
    }

    public function createImage(string $fileName): Image
    {
        return new Image(
            $fileName,
            '/dev/random',
            hash('sha256', $fileName),
            100,
            100,
            null,
        );
    }
}
