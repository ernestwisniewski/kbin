<?php declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\EntryCommentReport;
use App\Entity\EntryReport;
use App\Entity\PostCommentReport;
use App\Entity\PostReport;
use App\Event\Report\SubjectReportedEvent;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\EventDispatcher\EventDispatcherInterface;

class ReportFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function __construct(private EventDispatcherInterface $dispatcher,
    ) {

    }

    public function loadData(ObjectManager $manager): void
    {
        $this->entries();
        $this->entryComments();
        $this->posts();
        $this->postComments();

        $this->manager->flush();
    }

    private function entries()
    {
        $randomNb = $this->getUniqueNb(
            EntryFixtures::ENTRIES_COUNT,
            intval(EntryFixtures::ENTRIES_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $e) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new EntryReport(
                $this->getReference('user_'.$this->getRandomNumber(UserFixtures::USERS_COUNT)),
                $this->getReference('entry_'.$e)
            );

            $this->manager->persist($r);

            $this->dispatcher->dispatch(new SubjectReportedEvent($r));
        }
    }

    private function getUniqueNb(int $max, int $quantity): array
    {
        $numbers = range(1, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, $quantity);
    }

    public function getRandomNumber($max)
    {
        $numbers = range(1, $max);
        shuffle($numbers);

        return $numbers[0];
    }

    private function entryComments()
    {
        $randomNb = $this->getUniqueNb(
            EntryCommentFixtures::COMMENTS_COUNT,
            intval(EntryCommentFixtures::COMMENTS_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $c) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new EntryCommentReport(
                $this->getReference('user_'.$this->getRandomNumber(UserFixtures::USERS_COUNT)),
                $this->getReference('entry_comment_'.$c)
            );

            $this->manager->persist($r);

            $this->dispatcher->dispatch(new SubjectReportedEvent($r));
        }
    }

    private function posts()
    {
        $randomNb = $this->getUniqueNb(
            PostFixtures::ENTRIES_COUNT,
            intval(PostFixtures::ENTRIES_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $e) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new PostReport(
                $this->getReference('user_'.$this->getRandomNumber(UserFixtures::USERS_COUNT)),
                $this->getReference('post_'.$e)
            );

            $this->manager->persist($r);

            $this->dispatcher->dispatch(new SubjectReportedEvent($r));
        }
    }

    private function postComments()
    {
        $randomNb = $this->getUniqueNb(
            PostCommentFixtures::COMMENTS_COUNT,
            intval(PostCommentFixtures::COMMENTS_COUNT / rand(2, 5))
        );

        foreach ($randomNb as $c) {
            $roll = rand(0, 2);

            if (0 === $roll) {
                continue;
            }

            $r = new PostCommentReport(
                $this->getReference('user_'.$this->getRandomNumber(UserFixtures::USERS_COUNT)),
                $this->getReference('post_comment_'.$c)
            );

            $this->manager->persist($r);

            $this->dispatcher->dispatch(new SubjectReportedEvent($r));
        }
    }

    public function getDependencies(): array
    {
        return [
            EntryCommentFixtures::class,
            PostCommentFixtures::class,
        ];
    }
}
