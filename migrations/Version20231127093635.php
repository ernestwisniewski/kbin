<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231127093635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE broken_instance_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE remote_actor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE remote_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE remote_instance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE remote_actor (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE remote_group (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE remote_instance (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE broken_instance');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE remote_actor_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE remote_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE remote_instance_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE broken_instance_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE broken_instance (id INT NOT NULL, host VARCHAR(255) NOT NULL, exception TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX broken_instance_url_idx ON broken_instance (host)');
        $this->addSql('COMMENT ON COLUMN broken_instance.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('DROP TABLE remote_actor');
        $this->addSql('DROP TABLE remote_group');
        $this->addSql('DROP TABLE remote_instance');
    }
}
