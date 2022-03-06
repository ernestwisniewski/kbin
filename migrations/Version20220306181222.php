<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220306181222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD oauth_github_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD oauth_google_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD oauth_facebook_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP oauth_github_id');
        $this->addSql('ALTER TABLE "user" DROP oauth_google_id');
        $this->addSql('ALTER TABLE "user" DROP oauth_facebook_id');
    }
}
