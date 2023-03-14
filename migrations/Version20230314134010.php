<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314134010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO \"public\".\"award_type\" (\"id\", \"name\", \"category\", \"count\", \"attributes\") VALUES
(1, 'bronze_autobiographer', 'bronze', 0, 'a:0:{}'),
(2, 'bronze_personality', 'bronze', 0, 'a:0:{}'),
(3, 'bronze_commentator', 'bronze', 0, 'a:0:{}'),
(4, 'bronze_scout', 'bronze', 0, 'a:0:{}'),
(5, 'bronze_redactor', 'bronze', 0, 'a:0:{}'),
(6, 'bronze_poster', 'bronze', 0, 'a:0:{}'),
(7, 'bronze_link', 'bronze', 0, 'a:0:{}'),
(8, 'bronze_article', 'bronze', 0, 'a:0:{}'),
(9, 'bronze_photo', 'bronze', 0, 'a:0:{}'),
(10, 'bronze_comment', 'bronze', 0, 'a:0:{}'),
(11, 'bronze_post', 'bronze', 0, 'a:0:{}'),
(12, 'bronze_ranking', 'bronze', 0, 'a:0:{}'),
(13, 'bronze_popular_entry', 'bronze', 0, 'a:0:{}'),
(14, 'bronze_magazine', 'bronze', 0, 'a:0:{}'),
(15, 'silver_personality', 'silver', 0, 'a:0:{}'),
(16, 'silver_commentator', 'silver', 0, 'a:0:{}'),
(17, 'silver_scout', 'silver', 0, 'a:0:{}'),
(18, 'silver_redactor', 'silver', 0, 'a:0:{}'),
(19, 'silver_poster', 'silver', 0, 'a:0:{}'),
(20, 'silver_link', 'silver', 0, 'a:0:{}'),
(21, 'silver_article', 'silver', 0, 'a:0:{}'),
(22, 'silver_photo', 'silver', 0, 'a:0:{}'),
(23, 'silver_comment', 'silver', 0, 'a:0:{}'),
(24, 'silver_post', 'silver', 0, 'a:0:{}'),
(25, 'silver_ranking', 'silver', 0, 'a:0:{}'),
(26, 'silver_popular_entry', 'silver', 0, 'a:0:{}'),
(27, 'silver_magazine', 'silver', 0, 'a:0:{}'),
(28, 'silver_entry_week', 'silver', 0, 'a:0:{}'),
(29, 'silver_comment_week', 'silver', 0, 'a:0:{}'),
(30, 'silver_post_week', 'silver', 0, 'a:0:{}'),
(31, 'gold_personality', 'gold', 0, 'a:0:{}'),
(32, 'gold_commentator', 'gold', 0, 'a:0:{}'),
(33, 'gold_scout', 'gold', 0, 'a:0:{}'),
(34, 'gold_redactor', 'gold', 0, 'a:0:{}'),
(35, 'gold_poster', 'gold', 0, 'a:0:{}'),
(36, 'gold_link', 'gold', 0, 'a:0:{}'),
(37, 'gold_article', 'gold', 0, 'a:0:{}'),
(38, 'gold_photo', 'gold', 0, 'a:0:{}'),
(39, 'gold_comment', 'gold', 0, 'a:0:{}'),
(40, 'gold_post', 'gold', 0, 'a:0:{}'),
(41, 'gold_ranking', 'gold', 0, 'a:0:{}'),
(42, 'gold_popular_entry', 'gold', 0, 'a:0:{}'),
(43, 'gold_magazine', 'gold', 0, 'a:0:{}'),
(44, 'gold_entry_month', 'gold', 0, 'a:0:{}'),
(45, 'gold_comment_month', 'gold', 0, 'a:0:{}'),
(46, 'gold_post_month', 'gold', 0, 'a:0:{}');");
    }

    public function down(Schema $schema): void
    {
    }
}
