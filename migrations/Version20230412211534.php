<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230412211534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE magazine_subscription_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE magazine_subscription_request (id INT NOT NULL, user_id INT NOT NULL, magazine_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_38501651A76ED395 ON magazine_subscription_request (user_id)');
        $this->addSql('CREATE INDEX IDX_385016513EB84A1D ON magazine_subscription_request (magazine_id)');
        $this->addSql('CREATE UNIQUE INDEX magazine_subscription_requests_idx ON magazine_subscription_request (user_id, magazine_id)');
        $this->addSql('COMMENT ON COLUMN magazine_subscription_request.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE magazine_subscription_request ADD CONSTRAINT FK_38501651A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_subscription_request ADD CONSTRAINT FK_385016513EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE magazine_subscription_request_id_seq CASCADE');
        $this->addSql('ALTER TABLE magazine_subscription_request DROP CONSTRAINT FK_38501651A76ED395');
        $this->addSql('ALTER TABLE magazine_subscription_request DROP CONSTRAINT FK_385016513EB84A1D');
        $this->addSql('DROP TABLE magazine_subscription_request');
    }
}
