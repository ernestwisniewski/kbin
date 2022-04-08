<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220408100230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ADD edited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN entry.edited_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE entry_comment ADD edited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN entry_comment.edited_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE post ADD edited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN post.edited_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE post_comment ADD edited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN post_comment.edited_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry DROP edited_at');
        $this->addSql('ALTER TABLE entry_comment DROP edited_at');
        $this->addSql('ALTER TABLE post DROP edited_at');
        $this->addSql('ALTER TABLE post_comment DROP edited_at');
    }
}
