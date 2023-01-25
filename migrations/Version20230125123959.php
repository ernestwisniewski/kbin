<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230125123959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADB1174D2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_comment ADD root_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post_comment ADD update_mark BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT FK_A99CE55F79066886 FOREIGN KEY (root_id) REFERENCES post_comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A99CE55F79066886 ON post_comment (root_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CADB1174D2');
        $this->addSql('ALTER TABLE post_comment DROP CONSTRAINT FK_A99CE55F79066886');
        $this->addSql('DROP INDEX IDX_A99CE55F79066886');
        $this->addSql('ALTER TABLE post_comment DROP root_id');
        $this->addSql('ALTER TABLE post_comment DROP update_mark');
    }
}
