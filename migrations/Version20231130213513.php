<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130213513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_magazine_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE partner_block_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_private BOOLEAN DEFAULT false NOT NULL, is_official BOOLEAN DEFAULT false NOT NULL, magazines_count INT DEFAULT 0 NOT NULL, subscriptions_count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C1A76ED395 ON category (user_id)');
        $this->addSql('CREATE UNIQUE INDEX category_name_user_idx ON category (name, user_id)');
        $this->addSql('COMMENT ON COLUMN category.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE category_magazine (id INT NOT NULL, magazine_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_80F6AB9F3EB84A1D ON category_magazine (magazine_id)');
        $this->addSql('CREATE INDEX IDX_80F6AB9F12469DE2 ON category_magazine (category_id)');
        $this->addSql('CREATE UNIQUE INDEX category_magazine_idx ON category_magazine (magazine_id, category_id)');
        $this->addSql('CREATE TABLE category_subscription (id INT NOT NULL, user_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_79114741A76ED395 ON category_subscription (user_id)');
        $this->addSql('CREATE INDEX IDX_7911474112469DE2 ON category_subscription (category_id)');
        $this->addSql('CREATE UNIQUE INDEX category_subscription_idx ON category_subscription (user_id, category_id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_magazine ADD CONSTRAINT FK_80F6AB9F3EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_magazine ADD CONSTRAINT FK_80F6AB9F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_subscription ADD CONSTRAINT FK_79114741A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_subscription ADD CONSTRAINT FK_7911474112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE rememberme_token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_magazine_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE partner_block_id_seq CASCADE');
        $this->addSql('CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastused TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, class VARCHAR(100) NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY(series))');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1A76ED395');
        $this->addSql('ALTER TABLE category_magazine DROP CONSTRAINT FK_80F6AB9F3EB84A1D');
        $this->addSql('ALTER TABLE category_magazine DROP CONSTRAINT FK_80F6AB9F12469DE2');
        $this->addSql('ALTER TABLE category_subscription DROP CONSTRAINT FK_79114741A76ED395');
        $this->addSql('ALTER TABLE category_subscription DROP CONSTRAINT FK_7911474112469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_magazine');
        $this->addSql('DROP TABLE category_subscription');
    }
}
