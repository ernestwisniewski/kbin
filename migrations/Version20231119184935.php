<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119184935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX magazine_name_lower_idx ON magazine (lower(name))');
        $this->addSql('CREATE INDEX magazine_title_lower_idx ON magazine (lower(title))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX magazine_name_lower_idx');
        $this->addSql('DROP INDEX magazine_title_lower_idx');
    }
}
