<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220918140533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine ADD ap_followers_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ADD ap_preferred_username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ADD ap_discoverable BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ADD ap_manually_approves_followers BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ALTER private_key TYPE BOOLEAN');
        $this->addSql('ALTER TABLE "user" ADD ap_followers_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_preferred_username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_discoverable BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_manually_approves_followers BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER private_key TYPE BOOLEAN');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE magazine DROP ap_followers_url');
        $this->addSql('ALTER TABLE magazine DROP ap_preferred_username');
        $this->addSql('ALTER TABLE magazine DROP ap_discoverable');
        $this->addSql('ALTER TABLE magazine DROP ap_manually_approves_followers');
        $this->addSql('ALTER TABLE magazine ALTER private_key TYPE TEXT');
        $this->addSql('ALTER TABLE "user" DROP ap_followers_url');
        $this->addSql('ALTER TABLE "user" DROP ap_preferred_username');
        $this->addSql('ALTER TABLE "user" DROP ap_discoverable');
        $this->addSql('ALTER TABLE "user" DROP ap_manually_approves_followers');
        $this->addSql('ALTER TABLE "user" ALTER private_key TYPE TEXT');
    }
}
