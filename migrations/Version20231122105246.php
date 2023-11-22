<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122105246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD show_subscribed_users BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD show_subscribed_magazines BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD show_subscribed_domains BOOLEAN DEFAULT true NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP show_subscribed_users');
        $this->addSql('ALTER TABLE "user" DROP show_subscribed_magazines');
        $this->addSql('ALTER TABLE "user" DROP show_subscribed_domains');
    }
}
