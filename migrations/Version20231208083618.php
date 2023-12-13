<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231208083618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX user_entry_favourite_idx ON favourite (user_id, entry_id)');
        $this->addSql('CREATE UNIQUE INDEX user_entry_comment_favourite_idx ON favourite (user_id, entry_comment_id)');
        $this->addSql('CREATE UNIQUE INDEX user_post_favourite_idx ON favourite (user_id, post_id)');
        $this->addSql('CREATE UNIQUE INDEX user_post_comment_favourite_idx ON favourite (user_id, post_comment_id)');

        // DELETE FROM favourite
        // WHERE (entry_id, user_id) IN (
        //        SELECT entry_id, user_id
        //    FROM (
        //        SELECT entry_id, user_id
        //        FROM favourite
        //        GROUP BY entry_id, user_id
        //        HAVING COUNT(entry_id) > 1
        //        ORDER BY entry_id, user_id
        //    ) AS subquery
        // );
        //
        //
        // DELETE FROM favourite
        // WHERE (entry_comment_id, user_id) IN (
        //        SELECT entry_comment_id, user_id
        //    FROM (
        //        SELECT entry_comment_id, user_id
        //        FROM favourite
        //        GROUP BY entry_comment_id, user_id
        //        HAVING COUNT(entry_comment_id) > 1
        //        ORDER BY entry_comment_id, user_id
        //    ) AS subquery
        // );
        //
        // DELETE FROM favourite
        // WHERE (post_id, user_id) IN (
        //        SELECT post_id, user_id
        //    FROM (
        //        SELECT post_id, user_id
        //        FROM favourite
        //        GROUP BY post_id, user_id
        //        HAVING COUNT(post_id) > 1
        //        ORDER BY post_id, user_id
        //    ) AS subquery
        // );
        //
        //
        // DELETE FROM favourite
        // WHERE (post_comment_id, user_id) IN (
        //        SELECT post_comment_id, user_id
        //    FROM (
        //        SELECT post_comment_id, user_id
        //        FROM favourite
        //        GROUP BY post_comment_id, user_id
        //        HAVING COUNT(post_comment_id) > 1
        //        ORDER BY post_comment_id, user_id
        //    ) AS subquery
        // );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX user_entry_favourite_idx');
        $this->addSql('DROP INDEX user_entry_comment_favourite_idx');
        $this->addSql('DROP INDEX user_post_favourite_idx');
        $this->addSql('DROP INDEX user_post_comment_favourite_idx');
    }
}
