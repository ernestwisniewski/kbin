<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221116150037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER show_profile_subscriptions SET DEFAULT true');
        $this->addSql('ALTER TABLE "user" ALTER show_profile_followings SET DEFAULT true');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ALTER show_profile_subscriptions SET DEFAULT false');
        $this->addSql('ALTER TABLE "user" ALTER show_profile_followings SET DEFAULT false');
    }
}
