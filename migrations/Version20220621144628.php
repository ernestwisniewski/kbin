<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220621144628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE settings_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE settings (id INT NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE settings_id_seq CASCADE');
        $this->addSql('DROP TABLE settings');
    }
}
