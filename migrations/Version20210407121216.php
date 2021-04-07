<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407121216 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine ALTER custom_css TYPE TEXT');
        $this->addSql('ALTER TABLE magazine ALTER custom_css DROP DEFAULT');
        $this->addSql('ALTER TABLE magazine ALTER custom_js TYPE TEXT');
        $this->addSql('ALTER TABLE magazine ALTER custom_js DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE magazine ALTER custom_css TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE magazine ALTER custom_css DROP DEFAULT');
        $this->addSql('ALTER TABLE magazine ALTER custom_js TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE magazine ALTER custom_js DROP DEFAULT');
    }
}
