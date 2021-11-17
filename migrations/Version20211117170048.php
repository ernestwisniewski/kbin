<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211117170048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE cardano_payment_init_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cardano_payment_init (id INT NOT NULL, magazine_id INT DEFAULT NULL, user_id INT DEFAULT NULL, entry_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, cpi_type TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AFB29E403EB84A1D ON cardano_payment_init (magazine_id)');
        $this->addSql('CREATE INDEX IDX_AFB29E40A76ED395 ON cardano_payment_init (user_id)');
        $this->addSql('CREATE INDEX IDX_AFB29E40BA364942 ON cardano_payment_init (entry_id)');
        $this->addSql('COMMENT ON COLUMN cardano_payment_init.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE cardano_payment_init ADD CONSTRAINT FK_AFB29E403EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_payment_init ADD CONSTRAINT FK_AFB29E40A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_payment_init ADD CONSTRAINT FK_AFB29E40BA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cardano_payment_init_id_seq CASCADE');
        $this->addSql('DROP TABLE cardano_payment_init');
    }
}
