<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615091124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX entry_last_active_at_idx ON entry (last_active)');
        $this->addSql('CREATE INDEX entry_comment_up_votes_idx ON entry_comment (up_votes)');
        $this->addSql('CREATE INDEX entry_comment_last_active_at_idx ON entry_comment (last_active)');
        $this->addSql('CREATE INDEX entry_comment_created_at_idx ON entry_comment (created_at)');
        $this->addSql('CREATE INDEX post_last_active_at_idx ON post (last_active)');
        $this->addSql('CREATE INDEX post_comment_up_votes_idx ON post_comment (up_votes)');
        $this->addSql('CREATE INDEX post_comment_last_active_at_idx ON post_comment (last_active)');
        $this->addSql('CREATE INDEX post_comment_created_at_idx ON post_comment (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX post_last_active_at_idx');
        $this->addSql('DROP INDEX entry_last_active_at_idx');
        $this->addSql('DROP INDEX post_comment_up_votes_idx');
        $this->addSql('DROP INDEX post_comment_last_active_at_idx');
        $this->addSql('DROP INDEX post_comment_created_at_idx');
        $this->addSql('DROP INDEX entry_comment_up_votes_idx');
        $this->addSql('DROP INDEX entry_comment_last_active_at_idx');
        $this->addSql('DROP INDEX entry_comment_created_at_idx');
    }
}
