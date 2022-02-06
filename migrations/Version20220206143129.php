<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220206143129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE user_note_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_note (id INT NOT NULL, user_id INT NOT NULL, target_id INT NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B53CB6DDA76ED395 ON user_note (user_id)');
        $this->addSql('CREATE INDEX IDX_B53CB6DD158E0B66 ON user_note (target_id)');
        $this->addSql('CREATE UNIQUE INDEX user_noted_idx ON user_note (user_id, target_id)');
        $this->addSql('COMMENT ON COLUMN user_note.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE user_note ADD CONSTRAINT FK_B53CB6DDA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_note ADD CONSTRAINT FK_B53CB6DD158E0B66 FOREIGN KEY (target_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_note_id_seq CASCADE');
        $this->addSql('DROP TABLE user_note');
    }
}
