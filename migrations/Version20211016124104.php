<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211016124104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge ALTER magazine_id DROP NOT NULL');
        $this->addSql('ALTER TABLE badge ALTER name SET NOT NULL');
        $this->addSql('ALTER TABLE entry ALTER is_adult SET NOT NULL');
        $this->addSql('ALTER TABLE entry ALTER last_active SET NOT NULL');
        $this->addSql('ALTER TABLE entry_comment ALTER body SET NOT NULL');
        $this->addSql('ALTER TABLE magazine ALTER title SET NOT NULL');
        $this->addSql('ALTER TABLE magazine ALTER is_adult SET NOT NULL');
        $this->addSql('ALTER TABLE message_thread ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE post ALTER is_adult SET NOT NULL');
        $this->addSql('ALTER TABLE post ALTER last_active SET NOT NULL');
        $this->addSql('ALTER TABLE post_comment ALTER body SET NOT NULL');
        $this->addSql('ALTER TABLE site ADD domain VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE site ADD description TEXT NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER email SET NOT NULL');
        $this->addSql('ALTER TABLE view_counter ALTER entry_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE site DROP domain');
        $this->addSql('ALTER TABLE site DROP description');
        $this->addSql('ALTER TABLE "user" ALTER email DROP NOT NULL');
        $this->addSql('ALTER TABLE magazine ALTER title DROP NOT NULL');
        $this->addSql('ALTER TABLE magazine ALTER is_adult DROP NOT NULL');
        $this->addSql('ALTER TABLE badge ALTER magazine_id SET NOT NULL');
        $this->addSql('ALTER TABLE badge ALTER name DROP NOT NULL');
        $this->addSql('ALTER TABLE entry ALTER is_adult DROP NOT NULL');
        $this->addSql('ALTER TABLE entry ALTER last_active DROP NOT NULL');
        $this->addSql('ALTER TABLE entry_comment ALTER body DROP NOT NULL');
        $this->addSql('ALTER TABLE post ALTER is_adult DROP NOT NULL');
        $this->addSql('ALTER TABLE post ALTER last_active DROP NOT NULL');
        $this->addSql('ALTER TABLE post_comment ALTER body DROP NOT NULL');
        $this->addSql('ALTER TABLE message_thread ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE view_counter ALTER entry_id SET NOT NULL');
    }
}
