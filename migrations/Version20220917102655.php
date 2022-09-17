<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220917102655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_follow_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_follow_request (id INT NOT NULL, follower_id INT NOT NULL, following_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EE70876AC24F853 ON user_follow_request (follower_id)');
        $this->addSql('CREATE INDEX IDX_EE708761816E3A3 ON user_follow_request (following_id)');
        $this->addSql('CREATE UNIQUE INDEX user_follow_requests_idx ON user_follow_request (follower_id, following_id)');
        $this->addSql('COMMENT ON COLUMN user_follow_request.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE user_follow_request ADD CONSTRAINT FK_EE70876AC24F853 FOREIGN KEY (follower_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_follow_request ADD CONSTRAINT FK_EE708761816E3A3 FOREIGN KEY (following_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_follow_request_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_follow_request DROP CONSTRAINT FK_EE70876AC24F853');
        $this->addSql('ALTER TABLE user_follow_request DROP CONSTRAINT FK_EE708761816E3A3');
        $this->addSql('DROP TABLE user_follow_request');
    }
}
