<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220218220935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site ADD terms TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD privacy_policy TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site ALTER description DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE site DROP terms');
        $this->addSql('ALTER TABLE site DROP privacy_policy');
        $this->addSql('ALTER TABLE site ALTER description SET NOT NULL');
    }
}
