<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221108164813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2B219D70904F155E ON entry (ap_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B892FDFB904F155E ON entry_comment (ap_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_378C2FE4904F155E ON magazine (ap_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D904F155E ON post (ap_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A99CE55F904F155E ON post_comment (ap_id)');
        $this->addSql('ALTER TABLE "user" ALTER last_active DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649904F155E ON "user" (ap_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_B892FDFB904F155E');
        $this->addSql('DROP INDEX UNIQ_378C2FE4904F155E');
        $this->addSql('DROP INDEX UNIQ_2B219D70904F155E');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8D904F155E');
        $this->addSql('DROP INDEX UNIQ_8D93D649904F155E');
        $this->addSql('ALTER TABLE "user" ALTER last_active SET DEFAULT \'now()\'');
        $this->addSql('DROP INDEX UNIQ_A99CE55F904F155E');
    }
}
