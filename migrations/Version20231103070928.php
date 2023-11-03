<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103070928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE magazine ADD marked_for_deletion_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD marked_for_deletion_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD visibility TEXT DEFAULT \'visible\' NOT NULL');
        $this->addSql('CREATE INDEX user_visibility_idx ON "user" (visibility)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX user_visibility_idx');
        $this->addSql('ALTER TABLE "user" DROP marked_for_deletion_at');
        $this->addSql('ALTER TABLE "user" DROP visibility');
        $this->addSql('ALTER TABLE magazine DROP marked_for_deletion_at');
    }
}
