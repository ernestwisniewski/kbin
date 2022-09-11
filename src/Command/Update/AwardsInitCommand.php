<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Award;
use App\Entity\AwardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kbin:init:awards',
    description: 'This command allows init awards database.',
)]
class AwardsInitCommand extends Command
{
    private static $data = [
        'brown_autobiographer' => [],
        'brown_personality'    => [],
        'brown_commentator'    => [],
        'brown_scout'          => [],
        'brown_redactor'       => [],
        'brown_poster'         => [],
        'brown_link'           => [],
        'brown_article'        => [],
        'brown_photo'          => [],
        'brown_comment'        => [],
        'brown_post'           => [],
        'brown_ranking'        => [],
        'brown_popular_entry'  => [],
        'brown_magazine'       => [],
        'silver_personality'   => [],
        'silver_commentator'   => [],
        'silver_scout'         => [],
        'silver_redactor'      => [],
        'silver_poster'        => [],
        'silver_link'          => [],
        'silver_article'       => [],
        'silver_photo'         => [],
        'silver_comment'       => [],
        'silver_post'          => [],
        'silver_ranking'       => [],
        'silver_popular_entry' => [],
        'silver_magazine'      => [],
        'silver_entry_week'    => [],
        'silver_comment_week'  => [],
        'silver_post_week'     => [],
        'gold_personality'     => [],
        'gold_commentator'     => [],
        'gold_scout'           => [],
        'gold_redactor'        => [],
        'gold_poster'          => [],
        'gold_link'            => [],
        'gold_article'         => [],
        'gold_photo'           => [],
        'gold_comment'         => [],
        'gold_post'            => [],
        'gold_ranking'         => [],
        'gold_popular_entry'   => [],
        'gold_magazine'        => [],
        'gold_entry_month'     => [],
        'gold_comment_month'   => [],
        'gold_post_month'      => [],
    ];

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (self::$data as $index => $attr) {
            $award             = new AwardType();
            $award->name       = $index;
            $award->category   = explode('_', $index)[0];
            $award->attributes = $attr;

            $this->entityManager->persist($award);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
