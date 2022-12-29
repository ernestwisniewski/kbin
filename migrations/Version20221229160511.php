<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221229160511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site DROP domain');
        $this->addSql('ALTER TABLE site DROP title');
        $this->addSql('ALTER TABLE site DROP enabled');
        $this->addSql('ALTER TABLE site DROP registration_open');
        $this->addSql('ALTER TABLE site DROP description');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE site ADD domain VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE site ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE site ADD enabled BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE site ADD registration_open BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE site ADD description TEXT DEFAULT NULL');
    }
}
