<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231112062258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ALTER visibility TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE entry_comment ALTER visibility TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE magazine ALTER visibility TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE post ALTER visibility TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE post_comment ALTER visibility TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER visibility TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_comment ALTER visibility TYPE TEXT');
        $this->addSql('ALTER TABLE "user" ALTER visibility TYPE TEXT');
        $this->addSql('ALTER TABLE magazine ALTER visibility TYPE TEXT');
        $this->addSql('ALTER TABLE entry_comment ALTER visibility TYPE TEXT');
        $this->addSql('ALTER TABLE entry ALTER visibility TYPE TEXT');
        $this->addSql('ALTER TABLE post ALTER visibility TYPE TEXT');
    }
}
