<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231108084451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE magazine_ownership_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE moderator_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE magazine_ownership_request (id INT NOT NULL, user_id INT NOT NULL, magazine_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A7160C65A76ED395 ON magazine_ownership_request (user_id)');
        $this->addSql('CREATE INDEX IDX_A7160C653EB84A1D ON magazine_ownership_request (magazine_id)');
        $this->addSql('CREATE UNIQUE INDEX magazine_ownership_magazine_user_idx ON magazine_ownership_request (magazine_id, user_id)');
        $this->addSql('COMMENT ON COLUMN magazine_ownership_request.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE moderator_request (id INT NOT NULL, user_id INT NOT NULL, magazine_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2CC3E324A76ED395 ON moderator_request (user_id)');
        $this->addSql('CREATE INDEX IDX_2CC3E3243EB84A1D ON moderator_request (magazine_id)');
        $this->addSql('CREATE UNIQUE INDEX moderator_request_magazine_user_idx ON moderator_request (magazine_id, user_id)');
        $this->addSql('COMMENT ON COLUMN moderator_request.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE magazine_ownership_request ADD CONSTRAINT FK_A7160C65A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_ownership_request ADD CONSTRAINT FK_A7160C653EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moderator_request ADD CONSTRAINT FK_2CC3E324A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moderator_request ADD CONSTRAINT FK_2CC3E3243EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE magazine_ownership_request_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE moderator_request_id_seq CASCADE');
        $this->addSql('ALTER TABLE magazine_ownership_request DROP CONSTRAINT FK_A7160C65A76ED395');
        $this->addSql('ALTER TABLE magazine_ownership_request DROP CONSTRAINT FK_A7160C653EB84A1D');
        $this->addSql('ALTER TABLE moderator_request DROP CONSTRAINT FK_2CC3E324A76ED395');
        $this->addSql('ALTER TABLE moderator_request DROP CONSTRAINT FK_2CC3E3243EB84A1D');
        $this->addSql('DROP TABLE magazine_ownership_request');
        $this->addSql('DROP TABLE moderator_request');
    }
}
