<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230701125418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD preferred_languages JSONB NOT NULL DEFAULT \'[]\'::jsonb');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP preferred_languages');
    }
}
