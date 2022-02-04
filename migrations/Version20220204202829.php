<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204202829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE domain_block_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE domain_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE domain_block (id INT NOT NULL, user_id INT NOT NULL, domain_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5060BFF4A76ED395 ON domain_block (user_id)');
        $this->addSql('CREATE INDEX IDX_5060BFF4115F0EE5 ON domain_block (domain_id)');
        $this->addSql('CREATE UNIQUE INDEX domain_block_idx ON domain_block (user_id, domain_id)');
        $this->addSql('COMMENT ON COLUMN domain_block.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE domain_subscription (id INT NOT NULL, user_id INT NOT NULL, domain_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3AC9125EA76ED395 ON domain_subscription (user_id)');
        $this->addSql('CREATE INDEX IDX_3AC9125E115F0EE5 ON domain_subscription (domain_id)');
        $this->addSql('CREATE UNIQUE INDEX domain_subsription_idx ON domain_subscription (user_id, domain_id)');
        $this->addSql('COMMENT ON COLUMN domain_subscription.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE domain_block ADD CONSTRAINT FK_5060BFF4A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE domain_block ADD CONSTRAINT FK_5060BFF4115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE domain_subscription ADD CONSTRAINT FK_3AC9125EA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE domain_subscription ADD CONSTRAINT FK_3AC9125E115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE domain ADD subscriptions_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE domain_block_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE domain_subscription_id_seq CASCADE');
        $this->addSql('DROP TABLE domain_block');
        $this->addSql('DROP TABLE domain_subscription');
        $this->addSql('ALTER TABLE domain DROP subscriptions_count');
    }
}
