<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220116141404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD right_pos_images BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP right_pos_images');
    }
}
