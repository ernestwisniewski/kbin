<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615085154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX entry_title_ts_idx');
        $this->addSql('DROP INDEX entry_body_ts_idx');
        $this->addSql('ALTER TABLE entry DROP title_ts');
        $this->addSql('ALTER TABLE entry DROP body_ts');
        $this->addSql('CREATE INDEX entry_score_idx ON entry (score)');
        $this->addSql('CREATE INDEX entry_comment_count_idx ON entry (comment_count)');
        $this->addSql('DROP INDEX entry_comment_body_ts_idx');
        $this->addSql('ALTER TABLE entry_comment DROP body_ts');
        $this->addSql('DROP INDEX post_body_ts_idx');
        $this->addSql('ALTER TABLE post DROP body_ts');
        $this->addSql('CREATE INDEX post_score_idx ON post (score)');
        $this->addSql('CREATE INDEX post_comment_count_idx ON post (comment_count)');
        $this->addSql('DROP INDEX post_comment_body_ts_idx');
        $this->addSql('ALTER TABLE post_comment DROP body_ts');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX entry_score_idx');
        $this->addSql('DROP INDEX entry_comment_count_idx');
        $this->addSql('ALTER TABLE entry ADD title_ts TEXT DEFAULT \'english\'');
        $this->addSql('ALTER TABLE entry ADD body_ts TEXT DEFAULT \'english\'');
        $this->addSql('CREATE INDEX entry_title_ts_idx ON entry (title_ts)');
        $this->addSql('CREATE INDEX entry_body_ts_idx ON entry (body_ts)');
        $this->addSql('DROP INDEX post_score_idx');
        $this->addSql('DROP INDEX post_comment_count_idx');
        $this->addSql('ALTER TABLE post ADD body_ts TEXT DEFAULT \'english\'');
        $this->addSql('CREATE INDEX post_body_ts_idx ON post (body_ts)');
        $this->addSql('ALTER TABLE post_comment ADD body_ts TEXT DEFAULT \'english\'');
        $this->addSql('CREATE INDEX post_comment_body_ts_idx ON post_comment (body_ts)');
        $this->addSql('ALTER TABLE entry_comment ADD body_ts TEXT DEFAULT \'english\'');
        $this->addSql('CREATE INDEX entry_comment_body_ts_idx ON entry_comment (body_ts)');
    }
}
