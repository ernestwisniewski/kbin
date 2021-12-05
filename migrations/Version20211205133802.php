<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211205133802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CABA364942');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA60C33421');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA4B89032C');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CADB1174D2');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA537A1329');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA1255CD1D');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CABA364942 FOREIGN KEY (entry_id) REFERENCES entry (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA60C33421 FOREIGN KEY (entry_comment_id) REFERENCES entry_comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADB1174D2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA1255CD1D FOREIGN KEY (ban_id) REFERENCES magazine_ban (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476caba364942');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476ca60c33421');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476ca4b89032c');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476cadb1174d2');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476ca537a1329');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476ca1255cd1d');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476caba364942 FOREIGN KEY (entry_id) REFERENCES entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476ca60c33421 FOREIGN KEY (entry_comment_id) REFERENCES entry_comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476ca4b89032c FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476cadb1174d2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476ca537a1329 FOREIGN KEY (message_id) REFERENCES message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476ca1255cd1d FOREIGN KEY (ban_id) REFERENCES magazine_ban (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
