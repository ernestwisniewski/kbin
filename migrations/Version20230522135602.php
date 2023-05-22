<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522135602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX entry_visibility_adult_idx ON entry (visibility, is_adult)');
        $this->addSql('CREATE INDEX entry_visibility_idx ON entry (visibility)');
        $this->addSql('CREATE INDEX entry_adult_idx ON entry (is_adult)');
        $this->addSql('CREATE INDEX entry_ranking_idx ON entry (ranking)');
        $this->addSql('CREATE INDEX entry_created_at_idx ON entry (created_at)');
        $this->addSql('CREATE INDEX magazine_visibility_adult_idx ON magazine (visibility, is_adult)');
        $this->addSql('CREATE INDEX magazine_visibility_idx ON magazine (visibility)');
        $this->addSql('CREATE INDEX magazine_adult_idx ON magazine (is_adult)');
        $this->addSql('CREATE INDEX post_visibility_adult_idx ON post (visibility, is_adult)');
        $this->addSql('CREATE INDEX post_visibility_idx ON post (visibility)');
        $this->addSql('CREATE INDEX post_adult_idx ON post (is_adult)');
        $this->addSql('CREATE INDEX post_ranking_idx ON post (ranking)');
        $this->addSql('CREATE INDEX post_created_at_idx ON post (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX post_visibility_adult_idx');
        $this->addSql('DROP INDEX post_visibility_idx');
        $this->addSql('DROP INDEX post_adult_idx');
        $this->addSql('DROP INDEX post_ranking_idx');
        $this->addSql('DROP INDEX post_created_at_idx');
        $this->addSql('DROP INDEX magazine_visibility_adult_idx');
        $this->addSql('DROP INDEX magazine_visibility_idx');
        $this->addSql('DROP INDEX magazine_adult_idx');
        $this->addSql('DROP INDEX entry_visibility_adult_idx');
        $this->addSql('DROP INDEX entry_visibility_idx');
        $this->addSql('DROP INDEX entry_adult_idx');
        $this->addSql('DROP INDEX entry_ranking_idx');
        $this->addSql('DROP INDEX entry_created_at_idx');
    }
}
