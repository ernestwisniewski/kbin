<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230514143119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT FK_F2DE92908829462F');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT FK_F2DE9290A76ED395');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT FK_F2DE92908829462F FOREIGN KEY (message_thread_id) REFERENCES message_thread (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT FK_F2DE9290A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE settings ADD json JSONB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE settings DROP json');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT fk_f2de92908829462f');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT fk_f2de9290a76ed395');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT fk_f2de92908829462f FOREIGN KEY (message_thread_id) REFERENCES message_thread (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT fk_f2de9290a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
