<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Moderator;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchRepository
{
    public const PER_PAGE = 25;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function countModerated(User $user): int
    {
        $dql =
            'SELECT m FROM '.Magazine::class.' m WHERE m IN ('.
            'SELECT IDENTITY(md.magazine) FROM '.Moderator::class.' md WHERE md.user = :user) ORDER BY m.apId DESC, m.lastActive DESC';

        return \count(
            $this->entityManager->createQuery($dql)
                ->setParameter('user', $user)
                ->getResult()
        );
    }

    public function countBoosts(User $user): int
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT entry_id as id, created_at, 'entry' AS type FROM entry_vote WHERE user_id = :userId AND choice = 1)
        UNION
        (SELECT comment_id as id, created_at, 'entry_comment' AS type FROM entry_comment_vote WHERE user_id = :userId AND choice = 1)
        UNION
        (SELECT post_id as id, created_at, 'post' AS type FROM post_vote WHERE user_id = :userId AND choice = 1)
        UNION
        (SELECT comment_id as id, created_at, 'post_comment' AS type FROM post_comment_vote WHERE user_id = :userId AND choice = 1)
        ORDER BY created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $user->getId());
        $stmt = $stmt->executeQuery();

        return $stmt->rowCount();
    }

    public function findBoosts(int $page, User $user): PagerfantaInterface
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT entry_id as id, created_at, 'entry' AS type FROM entry_vote WHERE user_id = :userId AND choice = 1)
        UNION
        (SELECT comment_id as id, created_at, 'entry_comment' AS type FROM entry_comment_vote WHERE user_id = :userId AND choice = 1)
        UNION
        (SELECT post_id as id, created_at, 'post' AS type FROM post_vote WHERE user_id = :userId AND choice = 1)
        UNION
        (SELECT comment_id as id, created_at, 'post_comment' AS type FROM post_comment_vote WHERE user_id = :userId AND choice = 1)
        ORDER BY created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $user->getId());
        $stmt = $stmt->executeQuery();
        $stmt->rowCount();
        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $stmt->fetchAllAssociative()
            )
        );

        $countAll = $pagerfanta->count();

        try {
            $pagerfanta->setMaxPerPage(2000);
            $pagerfanta->setCurrentPage(1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $result = $pagerfanta->getCurrentPageResults();

        return $this->buildResult($result, $page, $countAll);
    }

    public function search($query, int $page = 1): PagerfantaInterface
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, created_at, visibility, 'entry' AS type FROM entry WHERE body_ts @@ plainto_tsquery( :query ) = true OR title_ts @@ plainto_tsquery( :query ) = true AND visibility = :visibility)
        UNION
        (SELECT id, created_at, visibility, 'entry_comment' AS type FROM entry_comment WHERE body_ts @@ plainto_tsquery( :query ) = true AND visibility = :visibility)
        UNION
        (SELECT id, created_at, visibility, 'post' AS type FROM post WHERE body_ts @@ plainto_tsquery( :query ) = true AND visibility = :visibility)
        UNION
        (SELECT id, created_at, visibility, 'post_comment' AS type FROM post_comment WHERE body_ts @@ plainto_tsquery( :query ) = true AND visibility = :visibility)
        ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('query', $query);
        $stmt->bindValue('visibility', VisibilityInterface::VISIBILITY_VISIBLE);
        $stmt = $stmt->executeQuery();

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $stmt->fetchAllAssociative()
            )
        );

        $countAll = $pagerfanta->count();

        try {
            $pagerfanta->setMaxPerPage(20000);
            $pagerfanta->setCurrentPage(1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $result = $pagerfanta->getCurrentPageResults();

        return $this->buildResult($result, $page, $countAll);
    }

    public function findByApId($url): array
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, created_at, 'entry' AS type FROM entry WHERE ap_id ILIKE :url) 
        UNION 
        (SELECT id, created_at, 'entry_comment' AS type FROM entry_comment WHERE ap_id ILIKE :url)
        UNION 
        (SELECT id, created_at, 'post' AS type FROM post WHERE ap_id ILIKE :url)
        UNION 
        (SELECT id, created_at, 'post_comment' AS type FROM post_comment WHERE ap_id ILIKE :url)
        ORDER BY created_at DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('url', '%'.$url.'%');
        $stmt = $stmt->executeQuery();

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $stmt->fetchAllAssociative()
            )
        );

        $countAll = $pagerfanta->count();

        try {
            $pagerfanta->setMaxPerPage(1);
            $pagerfanta->setCurrentPage(1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $result = $pagerfanta->getCurrentPageResults();

        $objects = [];
        if ($this->getOverviewIds((array) $result, 'entry')) {
            $objects = $this->entityManager->getRepository(Entry::class)->findBy(
                ['id' => $this->getOverviewIds((array) $result, 'entry')]
            );
        }
        if ($this->getOverviewIds((array) $result, 'entry_comment')) {
            $objects = $this->entityManager->getRepository(EntryComment::class)->findBy(
                ['id' => $this->getOverviewIds((array) $result, 'entry_comment')]
            );
        }
        if ($this->getOverviewIds((array) $result, 'post')) {
            $objects = $this->entityManager->getRepository(Post::class)->findBy(
                ['id' => $this->getOverviewIds((array) $result, 'post')]
            );
        }
        if ($this->getOverviewIds((array) $result, 'post_comment')) {
            $objects = $this->entityManager->getRepository(Post::class)->findBy(
                ['id' => $this->getOverviewIds((array) $result, 'post_comment')]
            );
        }

        return $objects ?? [];
    }

    private function getOverviewIds(array $result, string $type): array
    {
        $result = array_filter($result, fn ($subject) => $subject['type'] === $type);

        return array_map(fn ($subject) => $subject['id'], $result);
    }

    private function buildResult(array $result, $page, $countAll)
    {
        $entries = $this->entityManager->getRepository(Entry::class)->findBy(
            ['id' => $this->getOverviewIds((array) $result, 'entry')]
        );
        $entryComments = $this->entityManager->getRepository(EntryComment::class)->findBy(
            ['id' => $this->getOverviewIds((array) $result, 'entry_comment')]
        );
        $post = $this->entityManager->getRepository(Post::class)->findBy(
            ['id' => $this->getOverviewIds((array) $result, 'post')]
        );
        $postComment = $this->entityManager->getRepository(PostComment::class)->findBy(
            ['id' => $this->getOverviewIds((array) $result, 'post_comment')]
        );

        $result = array_merge($entries, $entryComments, $post, $postComment);
        uasort($result, fn ($a, $b) => $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $result
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
            $pagerfanta->setMaxNbPages($countAll > 0 ? ((int) ceil($countAll / self::PER_PAGE)) : 1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }
}
