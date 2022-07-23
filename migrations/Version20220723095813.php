<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220723095813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine ADD ap_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ADD ap_profile_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_profile_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER username TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE magazine DROP ap_id');
        $this->addSql('ALTER TABLE magazine DROP ap_profile_id');
        $this->addSql('ALTER TABLE "user" DROP ap_id');
        $this->addSql('ALTER TABLE "user" DROP ap_profile_id');
        $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE "user" ALTER username TYPE VARCHAR(35)');
    }
}
