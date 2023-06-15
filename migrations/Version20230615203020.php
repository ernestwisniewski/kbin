<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615203020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE entry ADD COLUMN title_ts tsvector GENERATED ALWAYS AS (to_tsvector('english', title)) STORED;");
        $this->addSql("ALTER TABLE entry ADD COLUMN body_ts tsvector GENERATED ALWAYS AS (to_tsvector('english', body)) STORED;");
        $this->addSql("ALTER TABLE post ADD COLUMN body_ts tsvector GENERATED ALWAYS AS (to_tsvector('english', body)) STORED;");
        $this->addSql("ALTER TABLE post_comment ADD COLUMN body_ts tsvector GENERATED ALWAYS AS (to_tsvector('english', body)) STORED;");
        $this->addSql("ALTER TABLE entry_comment ADD COLUMN body_ts tsvector GENERATED ALWAYS AS (to_tsvector('english', body)) STORED;");

        $this->addSql("CREATE INDEX entry_title_ts_idx ON entry USING GIN (title_ts);");
        $this->addSql("CREATE INDEX entry_body_ts_idx ON entry USING GIN (body_ts);");
        $this->addSql("CREATE INDEX post_body_ts_idx ON post USING GIN (body_ts);");
        $this->addSql("CREATE INDEX post_comment_body_ts_idx ON post_comment USING GIN (body_ts);");
        $this->addSql("CREATE INDEX entry_comment_body_ts_idx ON entry_comment USING GIN (body_ts);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE entry DROP title_ts');
        $this->addSql('ALTER TABLE entry DROP body_ts');
        $this->addSql('ALTER TABLE post DROP body_ts');
        $this->addSql('ALTER TABLE post_comment DROP body_ts');
        $this->addSql('ALTER TABLE entry_comment DROP body_ts');

        $this->addSql('DROP INDEX entry_title_ts_idx');
        $this->addSql('DROP INDEX entry_body_ts_idx');
        $this->addSql('DROP INDEX post_body_ts_idx');
        $this->addSql('DROP INDEX post_comment_body_ts_idx');
        $this->addSql('DROP INDEX entry_comment_body_ts_idx');
    }
}
