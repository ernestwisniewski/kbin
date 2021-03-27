<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210327183305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE entry_comment CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE magazine_ban CHANGE expired_at expired_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_comment CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE report CHANGE considered_at considered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE entry_comment CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE magazine_ban CHANGE expired_at expired_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_comment CHANGE last_active last_active DATETIME NOT NULL');
        $this->addSql('ALTER TABLE report CHANGE considered_at considered_at DATETIME DEFAULT NULL');
    }
}
