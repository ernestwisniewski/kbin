<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231111124634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry_comment ADD score INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX entry_comment_score_idx ON entry_comment (score)');
        $this->addSql('ALTER TABLE post_comment ADD score INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX post_comment_score_idx ON post_comment (score)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX post_comment_score_idx');
        $this->addSql('ALTER TABLE post_comment DROP score');
        $this->addSql('DROP INDEX entry_comment_score_idx');
        $this->addSql('ALTER TABLE entry_comment DROP score');
    }
}
