<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230428130129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine ADD ap_inbox_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE magazine ADD ap_domain VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_inbox_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ap_domain VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP ap_inbox_url');
        $this->addSql('ALTER TABLE "user" DROP ap_domain');
        $this->addSql('ALTER TABLE magazine DROP ap_inbox_url');
        $this->addSql('ALTER TABLE magazine DROP ap_domain');
    }
}
