<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220705184724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry ADD mentions JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD mentions JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD mentions JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD mentions JSONB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry DROP mentions');
        $this->addSql('ALTER TABLE post_comment DROP mentions');
        $this->addSql('ALTER TABLE entry_comment DROP mentions');
        $this->addSql('ALTER TABLE post DROP mentions');
    }
}
