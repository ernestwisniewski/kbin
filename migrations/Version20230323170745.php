<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323170745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry_comment ADD is_adult BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE entry_comment ALTER lang DROP DEFAULT');
        $this->addSql('ALTER TABLE post ALTER lang DROP DEFAULT');
        $this->addSql('ALTER TABLE post_comment ADD is_adult BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE post_comment ALTER lang DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_comment DROP is_adult');
        $this->addSql('ALTER TABLE post_comment ALTER lang SET DEFAULT \'pl\'');
        $this->addSql('ALTER TABLE entry_comment DROP is_adult');
        $this->addSql('ALTER TABLE entry_comment ALTER lang SET DEFAULT \'pl\'');
        $this->addSql('ALTER TABLE post ALTER lang SET DEFAULT \'pl\'');
    }
}
