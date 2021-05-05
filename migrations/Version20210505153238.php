<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505153238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entry ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD ip VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry DROP slug');
        $this->addSql('ALTER TABLE entry DROP ip');
        $this->addSql('ALTER TABLE entry_comment DROP ip');
        $this->addSql('ALTER TABLE post DROP slug');
        $this->addSql('ALTER TABLE post DROP ip');
        $this->addSql('ALTER TABLE post_comment DROP ip');
    }
}
