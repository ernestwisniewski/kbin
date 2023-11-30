<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\Pagination\KbinUnionPagination;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TagRepository
{
    public const PER_PAGE = 25;

    public function __construct(private EntityManagerInterface $entityManager, private CacheInterface $cache)
    {
    }

    public function findOverall(int $page, string $tag): PagerfantaInterface
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, created_at, 'entry' AS type FROM entry WHERE tags @> :tag = true AND visibility = :visibility)
        UNION
        (SELECT id, created_at, 'entry_comment' AS type FROM entry_comment WHERE tags @> :tag = true AND visibility = :visibility)
        UNION
        (SELECT id, created_at, 'post' AS type FROM post WHERE tags @> :tag = true AND visibility = :visibility)
        UNION
        (SELECT id, created_at, 'post_comment' AS type FROM post_comment WHERE tags @> :tag = true AND visibility = :visibility)
        ORDER BY created_at DESC LIMIT 25000";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('tag', "\"$tag\"");
        $stmt->bindValue('visibility', VisibilityInterface::VISIBILITY_VISIBLE);

        $result = $this->cache->get('tag_'.$tag, function (ItemInterface $item) use ($stmt) {
            $item->expiresAfter(30);
            $stmt = $stmt->executeQuery();

            return json_encode($stmt->fetchAllAssociative());
        });

        $result = json_decode($result, true);

        $countAll = \count($result);

        $startIndex = ($page - 1) * self::PER_PAGE;
        $result = \array_slice($result, $startIndex, self::PER_PAGE);

        $entries = $this->entityManager->getRepository(Entry::class)->findBy(
            ['id' => $this->getOverviewIds($result, 'entry')]
        );
        $entryComments = $this->entityManager->getRepository(EntryComment::class)->findBy(
            ['id' => $this->getOverviewIds($result, 'entry_comment')]
        );
        $post = $this->entityManager->getRepository(Post::class)->findBy(
            ['id' => $this->getOverviewIds($result, 'post')]
        );
        $postComment = $this->entityManager->getRepository(PostComment::class)->findBy(
            ['id' => $this->getOverviewIds($result, 'post_comment')]
        );

        $result = array_merge($entries, $entryComments, $post, $postComment);
        uasort($result, fn ($a, $b) => $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1);

        $pagerfanta = new KbinUnionPagination(
            new ArrayAdapter(
                $result
            )
        );

        try {
            $pagerfanta->setNbResults($countAll);
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    private function getOverviewIds(array $result, string $type): array
    {
        $result = array_filter($result, fn ($subject) => $subject['type'] === $type);

        return array_map(fn ($subject) => $subject['id'], $result);
    }
}
