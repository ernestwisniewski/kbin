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
use App\Kbin\Entry\EntryCrosspost;
use App\Kbin\Pagination\KbinUnionPagination;
use App\Kbin\SubjectOverviewListCreate;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\PagerfantaInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AggregateRepository
{
    public const SORT_DEFAULT = 'hot';
    public const TIME_DEFAULT = Criteria::TIME_ALL;
    public const PER_PAGE = 25;

    public function __construct(
        private EntryCrosspost $entryCrosspost,
        private SubjectOverviewListCreate $subjectOverviewListCreate,
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private readonly Security $security
    ) {
    }

    public function findByCriteria(Criteria $criteria, $withComments = false): PagerfantaInterface
    {
        // @todo union adapter
        $conn = $this->entityManager->getConnection();
        $sql = $this->getQuery($criteria, $withComments);
        $bind = [];

        $stmt = $conn->prepare($sql);

        $stmt->bindValue('visibility', VisibilityInterface::VISIBILITY_VISIBLE);
        $bind['visibility'] = VisibilityInterface::VISIBILITY_VISIBLE;

        //        $user = $this->security->getUser();
        //        if ($user) {
        //            $stmt->bindValue('private', VisibilityInterface::VISIBILITY_PRIVATE);
        //            $stmt->bindValue('user', $user->getId());
        //            $bind['private'] = VisibilityInterface::VISIBILITY_PRIVATE;
        //            $bind['user'] = $user->getId();
        //        }

        $user = $this->security->getUser();
        if ($user) {
            $stmt->bindValue('user', $user->getId());
            $bind['user'] = $user->getId();
        }
        if ($criteria->magazine) {
            $stmt->bindValue('magazine', $criteria->magazine->getId());
            $bind['magazine'] = $criteria->magazine->getId();
        }
        if ($criteria->user) {
            $stmt->bindValue('criteria_user', $criteria->user->getId());
            $bind['criteria_user'] = $criteria->user->getId();
        }
        if (Criteria::TIME_ALL !== $criteria->time) {
            $stmt->bindValue('timeFrom', $criteria->getRange()->from, Types::DATETIME_MUTABLE);
            $stmt->bindValue('timeTo', $criteria->getRange()->to, Types::DATETIME_MUTABLE);
            $bind['timeFrom'] = $criteria->getRange()->from;
            $bind['timeTo'] = $criteria->getRange()->to;
        }
        if ($criteria->category) {
            $stmt->bindValue('category', $criteria->category->getId());
            $bind['category'] = $criteria->category->getId();
        }
        if ($criteria->search) {
            $stmt->bindValue('search', $criteria->search);
            $bind['search'] = $criteria->search;
        }
        if ($criteria->tag) {
            $stmt->bindValue('tag', "\"$criteria->tag\"");
            $bind['tag'] = $criteria->tag;
        }
        if ($criteria->languages) {
            // @todo https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion
            foreach ($criteria->languages as $index => $language) {
                $stmt->bindValue("language_{$index}", $language);
                $bind["language_{$index}"] = $language;
            }
        }

        $result = $this->cache->get(
            $this->getCacheKey($sql, $bind),
            function (ItemInterface $item) use ($stmt, $criteria) {
                $item->expiresAfter(
                    \in_array($criteria->sortOption, [Criteria::SORT_NEW, Criteria::SORT_ACTIVE]) ? 30 : 300
                );
                $stmt = $stmt->executeQuery();

                return json_encode($stmt->fetchAllAssociative());
            }
        );

        $result = json_decode($result, true);

        $countAll = \count($result);

        $startIndex = ($criteria->page - 1) * self::PER_PAGE;
        $result = \array_slice($result, $startIndex, self::PER_PAGE);

        $entries = $this->entityManager->getRepository(Entry::class)->findBy(
            ['id' => $this->getOverviewIds($result, 'entry')]
        );
        $post = $this->entityManager->getRepository(Post::class)->findBy(
            ['id' => $this->getOverviewIds($result, 'post')]
        );

        if ($withComments) {
            $entryComments = $this->entityManager->getRepository(EntryComment::class)->findBy(
                ['id' => $this->getOverviewIds($result, 'entry_comment')]
            );
            $postComments = $this->entityManager->getRepository(PostComment::class)->findBy(
                ['id' => $this->getOverviewIds($result, 'post_comment')]
            );
        }

        $result = array_merge($entries, $post, $entryComments ?? [], $postComments ?? []);

        uasort(
            $result,
            fn ($a, $b) => $a->{$this->resolveSortField($criteria)} > $b->{$this->resolveSortField($criteria)} ? -1 : 1
        );

        $pagerfanta = new KbinUnionPagination(
            new ArrayAdapter(
                $result
            )
        );

        try {
            $pagerfanta->setNbResults($countAll);
            $pagerfanta->setMaxPerPage(self::PER_PAGE);
            $pagerfanta->setCurrentPage($criteria->page);

            $results = ($this->subjectOverviewListCreate)($pagerfanta, $criteria->sortOption);
            $pagerfanta->setCurrentPageResults($results);

            $results = $this->entryCrosspost->preparePageResults(
                $pagerfanta->getCurrentPageResults()
            );
            $pagerfanta->setCurrentPageResults($results);
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

    private function getQuery(Criteria $criteria, $withComments = false): string
    {
        $query = "
        ({$this->prepareQuery($criteria, 'entry')})
        UNION
        ({$this->prepareQuery($criteria, 'post')})
        ";

        if ($withComments) {
            $query .= "
        UNION
        ({$this->prepareCommentsQuery($criteria, 'entry_comment')})
        UNION
        ({$this->prepareCommentsQuery($criteria, 'post_comment')})
        ";
        }

        $query .= "ORDER BY {$this->resolveSortQuery($criteria)} DESC, created_at DESC LIMIT 25000";

        return $query;
    }

    private function prepareQuery(Criteria $criteria, string $type): string
    {
        return "
        SELECT {$type}.id, {$type}.created_at, {$type}.score, {$type}.ranking, {$type}.last_active, {$type}.comment_count, '{$type}' AS type 
        FROM {$type} 
        INNER JOIN magazine m_{$type} ON {$type}.magazine_id = m_{$type}.id 
        INNER JOIN ".'"user"'." u_{$type} ON {$type}.user_id = u_{$type}.id
        {$this->prepareWhereStatement($criteria, $type)}
        ";
    }

    private function prepareCommentsQuery(Criteria $criteria, string $type): string
    {
        return "
        SELECT {$type}.id, {$type}.created_at, {$type}.score, {$type}.score + {$type}.favourite_count AS ranking, {$type}.last_active, 0 as comment_count, '{$type}' AS type 
        FROM {$type} 
        INNER JOIN magazine m_{$type} ON {$type}.magazine_id = m_{$type}.id 
        INNER JOIN ".'"user"'." u_{$type} ON {$type}.user_id = u_{$type}.id
        {$this->prepareWhereStatement($criteria, $type)}
        ";
    }

    private function prepareWhereStatement(Criteria $criteria, string $type): string
    {
        //        $user = $this->security->getUser();
        //        if (!$user) {
        //            $where = "
        //            WHERE {$type}.visibility = :visibility
        //            AND m_{$type}.visibility = :visibility
        //            ";
        //        } else {
        //            $where = "
        //            WHERE (
        //                ({$type}.visibility = :visibility AND m_{$type}.visibility = :visibility)
        //                OR ({$type}.user_id IN (SELECT uflg_{$type}.following_id AS sclr_1 FROM user_follow uflg_{$type} WHERE uflg_{$type}.follower_id = :user AND {$type}.visibility = :private))
        //            )
        //            ";
        //        }

        $user = $this->security->getUser();
        if (!$user) {
            $where = "
            WHERE {$type}.visibility = :visibility
            AND m_{$type}.visibility = :visibility
            ";
        } else {
            $where = "
            WHERE (
            ({$type}.visibility = :visibility AND m_{$type}.visibility = :visibility)
            OR ({$type}.user_id = :user)
            )
            ";
        }

        if ($user && (!$criteria->magazine || !$criteria->magazine->userIsModerator($user)) && !$criteria->moderated) {
            $where .= "
            AND {$type}.user_id NOT IN (SELECT ub_{$type}.blocked_id AS sclr_2 FROM user_block ub_{$type} WHERE ub_{$type}.blocker_id = :user)
            AND {$type}.magazine_id NOT IN (SELECT mb_{$type}.magazine_id AS sclr_3 FROM magazine_block mb_{$type} WHERE mb_{$type}.user_id = :user)
            ";
        }

        if (Criteria::AP_LOCAL === $criteria->federation) {
            $where .= "
            AND {$type}.ap_id IS NULL
            ";
        }

        if ($criteria->magazine) {
            $where .= "
            AND {$type}.magazine_id = :magazine
            ";
        }

        if ($criteria->user) {
            $where .= "
            AND {$type}.user_id = :criteria_user
            ";
        }

        if ($criteria->subscribed) {
            $where .= "
            AND ({$type}.user_id = :user
            ";

            if ($criteria->showSubscribedUsers) {
                $where .= "
                OR {$type}.user_id IN (SELECT uflr_{$type}.following_id AS sclr_33 FROM user_follow uflr_{$type} WHERE uflr_{$type}.follower_id = :user)
                ";
            }

            if ($criteria->showSubscribedUsers) {
                $where .= "
                OR {$type}.magazine_id IN (SELECT ufm_{$type}.magazine_id AS sclr_34 FROM magazine_subscription ufm_{$type} WHERE ufm_{$type}.user_id = :user)
                ";
            }

            $where .= ')';
        }
        if ($criteria->moderated) {
            $where .= "
            AND {$type}.magazine_id IN (SELECT mm_{$type}.magazine_id AS sclr_33 FROM moderator mm_{$type} WHERE mm_{$type}.user_id = :user)
            ";
        }

        if ($criteria->favourite) {
            $where .= "
            AND {$type}.id IN (SELECT ef_{$type}.{$type}_id AS sclr_33 FROM favourite ef_{$type} WHERE (ef_{$type}.user_id = :user) AND ef_{$type}.favourite_type IN('".$type."'))
            ";
        }

        if ($criteria->category) {
            $where .= "
            AND {$type}.magazine_id IN (SELECT ct_{$type}.magazine_id AS sclr_33 FROM category_magazine ct_{$type} WHERE ct_{$type}.category_id = :category)
            ";
        }

        if ($criteria->search) {
            $where .= "
            AND {$type}.body_ts @@ plainto_tsquery( :search ) = true
            ";
        }

        if ($criteria->tag) {
            $where .= "
            AND {$type}.tags @> :tag = true
            ";
        }

        if (!$user || $user->hideAdult) {
            $where .= "
            AND {$type}.is_adult = false
            AND m_{$type}.is_adult = false
            ";
        }

        if (Criteria::TIME_ALL !== $criteria->time) {
            $where .= "
            AND ({$type}.created_at BETWEEN :timeFrom AND :timeTo)
            ";
        }

        if ($criteria->languages) {
            $where .= ' AND (';
            foreach ($criteria->languages as $index => $language) {
                $where .= "{$type}.lang = :language_{$index}";
                if ($index < \count($criteria->languages) - 1) {
                    $where .= ' OR ';
                }
            }
            $where .= ')';
        }

        return $where;
    }

    private function resolveSortQuery(Criteria $criteria): string
    {
        return match ($criteria->sortOption) {
            Criteria::SORT_TOP => 'score',
            Criteria::SORT_HOT => 'ranking',
            Criteria::SORT_COMMENTED => 'comment_count',
            Criteria::SORT_ACTIVE => 'last_active',
            default => 'created_at',
        };
    }

    private function resolveSortField(Criteria $criteria): string
    {
        return match ($criteria->sortOption) {
            Criteria::SORT_TOP => 'score',
            Criteria::SORT_HOT => 'ranking',
            Criteria::SORT_COMMENTED => 'commentCount',
            Criteria::SORT_ACTIVE => 'lastActive',
            default => 'createdAt',
        };
    }

    private function getCacheKey($query, $params): string
    {
        return 'pagination_union_'.hash('sha256', $query.json_encode($params));
    }
}
