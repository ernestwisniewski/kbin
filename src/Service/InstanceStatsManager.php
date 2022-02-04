<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
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

    public function count()
    {
        return $this->cache->get('instance_stats', function (ItemInterface $item) {
            $item->expiresAfter(60);

            return [
                'users'     => $this->userRepository->count([]),
                'magazines' => $this->magazineRepository->count([]),
                'entries'   => $this->entryRepository->count([]),
                'comments'  => $this->entryCommentRepository->count([]),
                'posts'     => $this->postRepository->count([]) + $this->postCommentRepository->count([]),
                'votes'     => $this->voteRepository->count(),
            ];
        });
    }

}
