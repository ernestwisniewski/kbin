<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202114605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ADD tags_tmp JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD tags_tmp JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD tags_tmp JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD tags_tmp JSONB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry_comment DROP tags_tmp');
        $this->addSql('ALTER TABLE entry DROP tags_tmp');
        $this->addSql('ALTER TABLE post DROP tags_tmp');
        $this->addSql('ALTER TABLE post_comment DROP tags_tmp');
    }
}
