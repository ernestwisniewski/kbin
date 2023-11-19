<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119115804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX magazine_ap_id_idx ON magazine (ap_id)');
        $this->addSql('CREATE INDEX magazine_ap_profile_id_idx ON magazine (ap_profile_id)');
        $this->addSql('CREATE INDEX user_ap_id_idx ON "user" (ap_id)');
        $this->addSql('CREATE INDEX user_ap_profile_id_idx ON "user" (ap_profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX user_ap_id_idx');
        $this->addSql('DROP INDEX user_ap_profile_id_idx');
        $this->addSql('DROP INDEX magazine_ap_id_idx');
        $this->addSql('DROP INDEX magazine_ap_profile_id_idx');
    }
}
