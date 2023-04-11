<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchRepository
{
    public const PER_PAGE = 50;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function search($query, int $page = 1): PagerfantaInterface
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, created_at, 'entry' AS type FROM entry WHERE body ILIKE '%".$query."%' OR title ILIKE '%".$query."%') 
        UNION 
        (SELECT id, created_at, 'entry_comment' AS type FROM entry_comment WHERE body ILIKE '%".$query."%')
        UNION 
        (SELECT id, created_at, 'post' AS type FROM post WHERE body ILIKE '%".$query."%')
        UNION 
        (SELECT id, created_at, 'post_comment' AS type FROM post_comment WHERE body ILIKE '%".$query."%')
        ORDER BY created_at DESC
        ";
        $stmt = $conn->prepare($sql);
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

        $entries = $this->entityManager->getRepository(Entry::class)->findBy(
            ['id' => $this->getOverviewIds((array)$result, 'entry')]
        );
        $this->entityManager->getRepository(Entry::class)->hydrate(...$entries);
        $entryComments = $this->entityManager->getRepository(EntryComment::class)->findBy(
            ['id' => $this->getOverviewIds((array)$result, 'entry_comment')]
        );
        $this->entityManager->getRepository(EntryComment::class)->hydrate(...$entryComments);
        $post = $this->entityManager->getRepository(Post::class)->findBy(
            ['id' => $this->getOverviewIds((array)$result, 'post')]
        );
        $this->entityManager->getRepository(Post::class)->hydrate(...$post);
        $postComment = $this->entityManager->getRepository(PostComment::class)->findBy(
            ['id' => $this->getOverviewIds((array)$result, 'post_comment')]
        );
        $this->entityManager->getRepository(PostComment::class)->hydrate(...$postComment);

        $result = array_merge($entries, $entryComments, $post, $postComment);
        uasort($result, fn($a, $b) => $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(
                $result
            )
        );

        try {
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($page);
            $pagerfanta->setMaxNbPages($countAll > 0 ? ((int)ceil($countAll / self::PER_PAGE)) : 1);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    public function findByApId($url): array
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = "
        (SELECT id, created_at, 'entry' AS type FROM entry WHERE ap_id ILIKE '%".$url."%') 
        UNION 
        (SELECT id, created_at, 'entry_comment' AS type FROM entry_comment WHERE ap_id ILIKE '%".$url."%')
        UNION 
        (SELECT id, created_at, 'post' AS type FROM post WHERE ap_id ILIKE '%".$url."%')
        UNION 
        (SELECT id, created_at, 'post_comment' AS type FROM post_comment WHERE ap_id ILIKE '%".$url."%')
        ORDER BY created_at DESC
        ";
        $stmt = $conn->prepare($sql);
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
        if ($this->getOverviewIds((array)$result, 'entry')) {
            $objects = $this->entityManager->getRepository(Entry::class)->findBy(
                ['id' => $this->getOverviewIds((array)$result, 'entry')]
            );
        }
        if ($this->getOverviewIds((array)$result, 'entry_comment')) {
            $objects = $this->entityManager->getRepository(EntryComment::class)->findBy(
                ['id' => $this->getOverviewIds((array)$result, 'entry_comment')]
            );
        }
        if ($this->getOverviewIds((array)$result, 'post')) {
            $objects = $this->entityManager->getRepository(Post::class)->findBy(
                ['id' => $this->getOverviewIds((array)$result, 'post')]
            );
        }
        if ($this->getOverviewIds((array)$result, 'post_comment')) {
            $objects = $this->entityManager->getRepository(Post::class)->findBy(
                ['id' => $this->getOverviewIds((array)$result, 'post_comment')]
            );
        }

        return $objects ?? [];
    }

    private function getOverviewIds(array $result, string $type): array
    {
        $result = array_filter($result, fn($subject) => $subject['type'] === $type);

        return array_map(fn($subject) => $subject['id'], $result);
    }
}
