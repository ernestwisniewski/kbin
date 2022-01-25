<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220125212007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ADD tags TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN entry.tags IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE entry_comment ADD tags TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN entry_comment.tags IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE post ADD tags TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN post.tags IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE post_comment ADD tags TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN post_comment.tags IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry_comment DROP tags');
        $this->addSql('ALTER TABLE post DROP tags');
        $this->addSql('ALTER TABLE post_comment DROP tags');
        $this->addSql('ALTER TABLE entry DROP tags');
    }
}
