<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApActivity;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\SettingsManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApActivity|null findOneByName(string $name)
 * @method ApActivity[]    findAll()
 * @method ApActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private SettingsManager $settingsManager)
    {
        parent::__construct($registry, ApActivity::class);
    }

    public function findByObjectId(string $apId): ?array
    {
        $parsed = parse_url($apId);
        if ($parsed['host'] === $this->settingsManager->get('KBIN_DOMAIN')) {
            $exploded = array_filter(explode('/', $parsed['path']));
            $id       = end($exploded);
            if ($exploded[3] === 'p') {
                if (count($exploded) === 4) {
                    return $this->_em->getRepository(Post::class)->find($id);
                } else {
                    return $this->_em->getRepository(PostComment::class)->find($id);
                }
            }

            if ($exploded[3] === 't') {
                if (count($exploded) === 4) {
                    return $this->_em->getRepository(Entry::class)->find($id);
                } else {
                    return $this->_em->getRepository(EntryComment::class)->find($id);
                }
            }
        }

        $entryClass        = Entry::class;
        $entryCommentClass = Entry::class;
        $postClass         = Post::class;
        $postCommentClass  = PostComment::class;

        $conn = $this->_em->getConnection();
        $sql  = "
        (SELECT id, '{$entryClass}' AS type FROM entry 
        WHERE ap_id = '{$apId}') 
        UNION 
        (SELECT id, '{$entryCommentClass}' AS type FROM entry_comment
        WHERE ap_id = '{$apId}')
        UNION 
        (SELECT id, '{$postClass}' AS type FROM post
        WHERE ap_id = '{$apId}')
        UNION 
        (SELECT id, '{$postCommentClass}' AS type FROM post_comment 
        WHERE ap_id = '{$apId}')
        ";

        $stmt = $conn->prepare($sql);

        $results = $stmt->executeQuery()->fetchAllAssociative();

        return count($results) ? $results[0] : null;
    }
}
