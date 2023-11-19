<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119205627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX entry_ap_id_idx ON entry (ap_id)');
        $this->addSql('CREATE INDEX entry_comment_ap_id_idx ON entry_comment (ap_id)');
        $this->addSql('CREATE INDEX post_ap_id_idx ON post (ap_id)');
        $this->addSql('CREATE INDEX post_comment_ap_id_idx ON post_comment (ap_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX post_ap_id_idx');
        $this->addSql('DROP INDEX post_comment_ap_id_idx');
        $this->addSql('DROP INDEX entry_ap_id_idx');
        $this->addSql('DROP INDEX entry_comment_ap_id_idx');
    }
}
