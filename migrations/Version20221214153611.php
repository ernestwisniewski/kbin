<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221214153611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX domain_subsription_idx RENAME TO domain_subscription_idx');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT FK_87D3D4C51255CD1D');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT FK_87D3D4C51255CD1D FOREIGN KEY (ban_id) REFERENCES magazine_ban (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT FK_F2DE92908829462F');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT FK_F2DE9290A76ED395');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT FK_F2DE92908829462F FOREIGN KEY (message_thread_id) REFERENCES message_thread (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT FK_F2DE9290A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT fk_bf5476cadb1174d2');
        $this->addSql('ALTER TABLE "user" ALTER hide_user_avatars SET DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT fk_bf5476cadb1174d2 FOREIGN KEY (post_comment_id) REFERENCES post_comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX domain_subscription_idx RENAME TO domain_subsription_idx');
        $this->addSql('ALTER TABLE "user" ALTER hide_user_avatars SET DEFAULT true');
        $this->addSql('ALTER TABLE magazine_log DROP CONSTRAINT fk_87d3d4c51255cd1d');
        $this->addSql('ALTER TABLE magazine_log ADD CONSTRAINT fk_87d3d4c51255cd1d FOREIGN KEY (ban_id) REFERENCES magazine_ban (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT fk_f2de92908829462f');
        $this->addSql('ALTER TABLE message_thread_participants DROP CONSTRAINT fk_f2de9290a76ed395');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT fk_f2de92908829462f FOREIGN KEY (message_thread_id) REFERENCES message_thread (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_thread_participants ADD CONSTRAINT fk_f2de9290a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
