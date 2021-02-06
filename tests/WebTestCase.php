<?php declare(strict_types=1);

namespace App\Tests;

use App\DTO\EntryCommentDto;
use App\Service\EntryCommentManager;
use App\Service\VoteManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EntryCommentVote;
use App\Service\MagazineManager;
use App\Service\EntryManager;
use App\Entity\EntryComment;
use App\Entity\EntryVote;
use App\DTO\MagazineDto;
use App\Entity\Magazine;
use App\DTO\EntryDto;
use App\Entity\Entry;
use App\Entity\Vote;
use App\Entity\User;

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

    protected function loadExampleUsers(): void
    {
        foreach ($this->provideUsers() as $data) {
            $this->createUser($data['username'], $data['email'], $data['password']);
        }
    }

    protected function getUserByUsername(string $username): User
    {
        $user = $this->users->filter(
            static function (User $user) use ($username) {
                return $user->getUsername() === $username;
            }
        )->first();

        return $user ? $user : $this->createUser($username);
    }

    private function createUser(string $username, string $email = null, string $password = null): User
    {
        $manager = self::$container->get(EntityManagerInterface::class);

        $user = new User($email ? $email : $username.'@example.com', $username, $password ? $password : 'secret');

        $manager->persist($user);
        $manager->flush();

        $this->users->add($user);

        return $user;
    }

    protected function loadExampleMagazines(): void
    {
        $this->loadExampleUsers();

        foreach ($this->provideMagazines() as $data) {
            $this->createMagazine($data['name'], $data['title'], $data['user']);
        }
    }

    protected function getMagazineByName(string $name, ?User $user = null): Magazine
    {
        $magazine = $this->magazines->filter(
            static function (Magazine $magazine) use ($name) {
                return $magazine->getName() === $name;
            }
        )->first();

        return $magazine ? $magazine : $this->createMagazine($name);
    }

    private function createMagazine(string $name, string $title = null, User $user = null): Magazine
    {
        /**
         * @var $manager MagazineManager
         */
        $manager = self::$container->get(MagazineManager::class);

        $dto      = (new MagazineDto())->create($name, $title ?? 'Przykładowy magazyn');
        $magazine = $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));

        $this->magazines->add($magazine);

        return $magazine;
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
                return $entry->getTitle() === $title;
            }
        )->first();

        if (!$entry) {
            $magazine = $magazine ?? $this->getMagazineByName('polityka');
            $user     = $user ?? $this->getUserByUsername('regularUser');
            $entry    = $this->createEntry($title, $magazine, $user, $url, $body);
        }

        return $entry;
    }

    public function createEntryComment(string $body, ?Entry $entry = null, ?User $user = null, ?EntryComment $parent = null): EntryComment
    {
        /**
         * @var $manager EntryCommentManager
         */
        $manager = self::$container->get(EntryCommentManager::class);

        if ($parent) {
            $dto = (new EntryCommentDto())->createWithParent($entry ?? $this->getEntryByTitle('Przykladowa treść'), $parent, $body);
        } else {
            $dto = (new EntryCommentDto())->create($entry ?? $this->getEntryByTitle('Przykladowa treść'), $body);
        }

        return $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));
    }

    private function createEntry(string $title, Magazine $magazine, User $user, ?string $url = null, ?string $body = 'testowa treść'): Entry
    {
        /**
         * @var $manager EntryManager
         */
        $manager = self::$container->get(EntryManager::class);

        $dto   = (new EntryDto())->create($magazine, $title, $url, $body);
        $entry = $manager->create($dto, $user ?? $this->getUserByUsername('regularUser'));

        $this->entries->add($entry);

        return $entry;
    }

    public function createEntryVote(int $choice, Entry $entry, User $user): Vote
    {
        $entityManager = self::$container->get(EntityManagerInterface::class);
        /**
         * @var $voteManager VoteManager
         */
        $voteManager = self::$container->get(VoteManager::class);

        $vote = $voteManager->vote($choice, $entry, $user);

        $entityManager->persist($vote);
        $entityManager->flush();

        return $vote;
    }

    public function createEntryCommentVote(int $choice, EntryComment $comment, User $user): Vote
    {
        $manager = self::$container->get(EntityManagerInterface::class);
        /**
         * @var $voteManager VoteManager
         */
        $voteManager = self::$container->get(VoteManager::class);

        $vote = $voteManager->vote($choice, $comment, $user);

        $manager->persist($vote);
        $manager->flush();

        return $vote;
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
}
