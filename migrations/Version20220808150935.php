<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220808150935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ADD ap_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD ap_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD ap_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD ap_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry DROP ap_id');
        $this->addSql('ALTER TABLE entry_comment DROP ap_id');
        $this->addSql('ALTER TABLE post DROP ap_id');
        $this->addSql('ALTER TABLE post_comment DROP ap_id');
    }
}
