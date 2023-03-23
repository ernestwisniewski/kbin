<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323160934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ALTER lang SET NOT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD lang VARCHAR(255) DEFAULT \'pl\' NOT NULL');
        $this->addSql('ALTER TABLE post ADD lang VARCHAR(255) DEFAULT \'pl\' NOT NULL');
        $this->addSql('ALTER TABLE post_comment ADD lang VARCHAR(255) DEFAULT \'pl\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_comment DROP lang');
        $this->addSql('ALTER TABLE entry_comment DROP lang');
        $this->addSql('ALTER TABLE entry ALTER lang DROP NOT NULL');
        $this->addSql('ALTER TABLE post DROP lang');
    }
}
