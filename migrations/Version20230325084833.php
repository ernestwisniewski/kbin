<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230325084833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry_comment ALTER is_adult DROP DEFAULT');
        $this->addSql('ALTER TABLE magazine DROP CONSTRAINT fk_378c2fe4922726e9');
        $this->addSql('DROP INDEX idx_378c2fe4922726e9');
        $this->addSql('ALTER TABLE magazine RENAME COLUMN cover_id TO icon_id');
        $this->addSql('ALTER TABLE magazine ADD CONSTRAINT FK_378C2FE454B9D732 FOREIGN KEY (icon_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_378C2FE454B9D732 ON magazine (icon_id)');
        $this->addSql('ALTER TABLE post_comment ALTER is_adult DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" DROP hide_images');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry_comment ALTER is_adult SET DEFAULT false');
        $this->addSql('ALTER TABLE magazine DROP CONSTRAINT FK_378C2FE454B9D732');
        $this->addSql('DROP INDEX IDX_378C2FE454B9D732');
        $this->addSql('ALTER TABLE magazine RENAME COLUMN icon_id TO cover_id');
        $this->addSql('ALTER TABLE magazine ADD CONSTRAINT fk_378c2fe4922726e9 FOREIGN KEY (cover_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_378c2fe4922726e9 ON magazine (cover_id)');
        $this->addSql('ALTER TABLE post_comment ALTER is_adult SET DEFAULT false');
        $this->addSql('ALTER TABLE "user" ADD hide_images BOOLEAN DEFAULT false NOT NULL');
    }
}
