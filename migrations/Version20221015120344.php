<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221015120344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE image ADD blurhash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD alt_text VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD source_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image DROP blurhash');
        $this->addSql('ALTER TABLE image DROP alt_text');
        $this->addSql('ALTER TABLE image DROP source_url');
    }
}
