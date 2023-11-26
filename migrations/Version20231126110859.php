<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231126110859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE view_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE view (id INT NOT NULL, user_id INT NOT NULL, entry_id INT DEFAULT NULL, post_id INT DEFAULT NULL, last_active TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FEFDAB8EA76ED395 ON view (user_id)');
        $this->addSql('CREATE INDEX IDX_FEFDAB8EBA364942 ON view (entry_id)');
        $this->addSql('CREATE INDEX IDX_FEFDAB8E4B89032C ON view (post_id)');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8EA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8EBA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE view_id_seq CASCADE');
        $this->addSql('ALTER TABLE view DROP CONSTRAINT FK_FEFDAB8EA76ED395');
        $this->addSql('ALTER TABLE view DROP CONSTRAINT FK_FEFDAB8EBA364942');
        $this->addSql('ALTER TABLE view DROP CONSTRAINT FK_FEFDAB8E4B89032C');
        $this->addSql('DROP TABLE view');
    }
}
