<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221124162526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT FK_87D3D4C51255CD1D');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT FK_87D3D4C54B89032C');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT FK_87D3D4C560C33421');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT FK_87D3D4C5BA364942');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT FK_87D3D4C5DB1174D2');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT FK_87D3D4C51255CD1D FOREIGN KEY (ban_id) REFERENCES magazine_ban (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT FK_87D3D4C54B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT FK_87D3D4C560C33421 FOREIGN KEY (entry_comment_id) REFERENCES entry_comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT FK_87D3D4C5BA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT FK_87D3D4C5DB1174D2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476caa76ed395');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476caa76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT fk_87d3d4c5ba364942');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT fk_87d3d4c560c33421');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT fk_87d3d4c54b89032c');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT fk_87d3d4c5db1174d2');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT fk_87d3d4c51255cd1d');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT fk_87d3d4c5ba364942 FOREIGN KEY (entry_id) REFERENCES entry (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT fk_87d3d4c560c33421 FOREIGN KEY (entry_comment_id) REFERENCES entry_comment (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT fk_87d3d4c54b89032c FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT fk_87d3d4c5db1174d2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT fk_87d3d4c51255cd1d FOREIGN KEY (ban_id) REFERENCES magazine_ban (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476caa76ed395');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476caa76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
