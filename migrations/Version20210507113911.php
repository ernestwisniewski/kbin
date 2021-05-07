<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210507113911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE site_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE site (id INT NOT NULL, title VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, registration_open BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE entry ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entry ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entry ALTER sticky DROP DEFAULT');
        $this->addSql('ALTER TABLE entry_comment ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD ip VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE site_id_seq CASCADE');
        $this->addSql('DROP TABLE site');
        $this->addSql('ALTER TABLE entry DROP slug');
        $this->addSql('ALTER TABLE entry DROP ip');
        $this->addSql('ALTER TABLE entry ALTER sticky SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE entry_comment DROP ip');
        $this->addSql('ALTER TABLE post DROP slug');
        $this->addSql('ALTER TABLE post DROP ip');
        $this->addSql('ALTER TABLE post_comment DROP ip');
    }
}
