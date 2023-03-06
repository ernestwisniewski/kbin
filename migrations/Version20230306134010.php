<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306134010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP theme');
        $this->addSql('ALTER TABLE "user" DROP mode');
        $this->addSql('ALTER TABLE "user" DROP right_pos_images');
        $this->addSql('ALTER TABLE "user" DROP hide_user_avatars');
        $this->addSql('ALTER TABLE "user" DROP hide_magazine_avatars');
        $this->addSql('ALTER TABLE "user" DROP entry_popup');
        $this->addSql('ALTER TABLE "user" DROP post_popup');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ADD theme VARCHAR(255) DEFAULT \'dark\' NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD mode VARCHAR(255) DEFAULT \'normal\' NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD right_pos_images BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD hide_user_avatars BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD hide_magazine_avatars BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD entry_popup BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD post_popup BOOLEAN DEFAULT false NOT NULL');
    }
}
