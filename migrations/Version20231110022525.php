<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231110022525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE view_counter_id_seq CASCADE');
        $this->addSql('ALTER TABLE view_counter DROP CONSTRAINT fk_e87f8182ba364942');
        $this->addSql('DROP TABLE view_counter');
        $this->addSql('ALTER TABLE entry DROP views');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE view_counter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE view_counter (id INT NOT NULL, entry_id INT DEFAULT NULL, ip TEXT NOT NULL, view_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e87f8182ba364942 ON view_counter (entry_id)');
        $this->addSql('ALTER TABLE view_counter ADD CONSTRAINT fk_e87f8182ba364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE entry ADD views INT DEFAULT NULL');
    }
}
