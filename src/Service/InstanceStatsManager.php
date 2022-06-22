<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class InstanceStatsManager
{
    public function __construct(
        private UserRepository $userRepository,
        private MagazineRepository $magazineRepository,
        private EntryRepository $entryRepository,
        private EntryCommentRepository $entryCommentRepository,
        private PostRepository $postRepository,
        private PostCommentRepository $postCommentRepository,
        private VoteRepository $voteRepository,
        private CacheInterface $cache
    ) {
    }

    public function count(?string $period = null)
    {
        $period = $period ? DateTimeImmutable::createFromMutable(new \DateTime($period)) : null;

        return $this->cache->get('instance_stats', function (ItemInterface $item) use ($period) {
            $item->expiresAfter(0);

            $criteria = Criteria::create();

            if ($period) {
                $criteria->where(
                    Criteria::expr()
                        ->gt('createdAt', $period)
                );
            }

            return [
                'users'     => $this->userRepository->matching($criteria)->count(),
                'magazines' => $this->magazineRepository->matching($criteria)->count(),
                'entries'   => $this->entryRepository->matching($criteria)->count(),
                'comments'  => $this->entryCommentRepository->matching($criteria)->count(),
                'posts'     => $this->postRepository->matching($criteria)->count() + $this->postCommentRepository->matching($criteria)->count(),
                'votes'     => $this->voteRepository->count($period),
            ];
        });
    }

}
