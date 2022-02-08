<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220208192443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE favourite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE favourite (id INT NOT NULL, magazine_id INT NOT NULL, user_id INT NOT NULL, entry_id INT DEFAULT NULL, entry_comment_id INT DEFAULT NULL, post_id INT DEFAULT NULL, post_comment_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, favourite_type TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_62A2CA193EB84A1D ON favourite (magazine_id)');
        $this->addSql('CREATE INDEX IDX_62A2CA19A76ED395 ON favourite (user_id)');
        $this->addSql('CREATE INDEX IDX_62A2CA19BA364942 ON favourite (entry_id)');
        $this->addSql('CREATE INDEX IDX_62A2CA1960C33421 ON favourite (entry_comment_id)');
        $this->addSql('CREATE INDEX IDX_62A2CA194B89032C ON favourite (post_id)');
        $this->addSql('CREATE INDEX IDX_62A2CA19DB1174D2 ON favourite (post_comment_id)');
        $this->addSql('COMMENT ON COLUMN favourite.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE favourite ADD CONSTRAINT FK_62A2CA193EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favourite ADD CONSTRAINT FK_62A2CA19A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favourite ADD CONSTRAINT FK_62A2CA19BA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favourite ADD CONSTRAINT FK_62A2CA1960C33421 FOREIGN KEY (entry_comment_id) REFERENCES entry_comment (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favourite ADD CONSTRAINT FK_62A2CA194B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favourite ADD CONSTRAINT FK_62A2CA19DB1174D2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE entry ADD favourite_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE entry_comment ADD favourite_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post ADD favourite_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post_comment ADD favourite_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE favourite_id_seq CASCADE');
        $this->addSql('DROP TABLE favourite');
        $this->addSql('ALTER TABLE entry DROP favourite_count');
        $this->addSql('ALTER TABLE entry_comment DROP favourite_count');
        $this->addSql('ALTER TABLE post DROP favourite_count');
        $this->addSql('ALTER TABLE post_comment DROP favourite_count');
    }
}
