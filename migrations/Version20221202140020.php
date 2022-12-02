<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202140020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry RENAME COLUMN tags_tmp TO tags');
        $this->addSql('ALTER TABLE entry_comment RENAME COLUMN tags_tmp TO tags');
        $this->addSql('ALTER TABLE post RENAME COLUMN tags_tmp TO tags');
        $this->addSql('ALTER TABLE post_comment RENAME COLUMN tags_tmp TO tags');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry RENAME COLUMN tags TO tags_tmp');
        $this->addSql('ALTER TABLE entry_comment RENAME COLUMN tags TO tags_tmp');
        $this->addSql('ALTER TABLE post RENAME COLUMN tags TO tags_tmp');
        $this->addSql('ALTER TABLE post_comment RENAME COLUMN tags TO tags_tmp');
    }
}
