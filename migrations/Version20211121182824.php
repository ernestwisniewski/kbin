<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211121182824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE cardano_payment_init_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE cardano_tx_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cardano_tx_init_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cardano_tx (id INT NOT NULL, magazine_id INT DEFAULT NULL, receiver_id INT DEFAULT NULL, sender_id INT DEFAULT NULL, entry_id INT DEFAULT NULL, amount INT NOT NULL, tx_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, ctx_type TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F74C620E3EB84A1D ON cardano_tx (magazine_id)');
        $this->addSql('CREATE INDEX IDX_F74C620ECD53EDB6 ON cardano_tx (receiver_id)');
        $this->addSql('CREATE INDEX IDX_F74C620EF624B39D ON cardano_tx (sender_id)');
        $this->addSql('CREATE INDEX IDX_F74C620EBA364942 ON cardano_tx (entry_id)');
        $this->addSql('COMMENT ON COLUMN cardano_tx.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE cardano_tx_init (id INT NOT NULL, magazine_id INT DEFAULT NULL, user_id INT DEFAULT NULL, entry_id INT DEFAULT NULL, session_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, cpi_type TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_973316583EB84A1D ON cardano_tx_init (magazine_id)');
        $this->addSql('CREATE INDEX IDX_97331658A76ED395 ON cardano_tx_init (user_id)');
        $this->addSql('CREATE INDEX IDX_97331658BA364942 ON cardano_tx_init (entry_id)');
        $this->addSql('COMMENT ON COLUMN cardano_tx_init.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE cardano_tx ADD CONSTRAINT FK_F74C620E3EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_tx ADD CONSTRAINT FK_F74C620ECD53EDB6 FOREIGN KEY (receiver_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_tx ADD CONSTRAINT FK_F74C620EF624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_tx ADD CONSTRAINT FK_F74C620EBA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_tx_init ADD CONSTRAINT FK_973316583EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_tx_init ADD CONSTRAINT FK_97331658A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_tx_init ADD CONSTRAINT FK_97331658BA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE cardano_payment_init');
        $this->addSql('ALTER TABLE entry ADD ada_amount INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD cardano_wallet_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cardano_tx_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cardano_tx_init_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE cardano_payment_init_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cardano_payment_init (id INT NOT NULL, magazine_id INT DEFAULT NULL, user_id INT DEFAULT NULL, entry_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, cpi_type TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_afb29e40ba364942 ON cardano_payment_init (entry_id)');
        $this->addSql('CREATE INDEX idx_afb29e40a76ed395 ON cardano_payment_init (user_id)');
        $this->addSql('CREATE INDEX idx_afb29e403eb84a1d ON cardano_payment_init (magazine_id)');
        $this->addSql('COMMENT ON COLUMN cardano_payment_init.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE cardano_payment_init ADD CONSTRAINT fk_afb29e403eb84a1d FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_payment_init ADD CONSTRAINT fk_afb29e40a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cardano_payment_init ADD CONSTRAINT fk_afb29e40ba364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE cardano_tx');
        $this->addSql('DROP TABLE cardano_tx_init');
        $this->addSql('ALTER TABLE "user" DROP cardano_wallet_address');
        $this->addSql('ALTER TABLE entry DROP ada_amount');
    }
}
