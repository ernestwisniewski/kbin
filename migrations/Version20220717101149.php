<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220717101149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine ADD private_key TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ADD public_key TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD private_key TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD public_key TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE magazine DROP private_key');
        $this->addSql('ALTER TABLE magazine DROP public_key');
        $this->addSql('ALTER TABLE "user" DROP private_key');
        $this->addSql('ALTER TABLE "user" DROP public_key');
    }
}
