<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210117131953 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, magazine_id INT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, INDEX IDX_2B219D70A76ED395 (user_id), INDEX IDX_2B219D703EB84A1D (magazine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE magazine (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moderator (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, magazine_id INT NOT NULL, is_owner TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_6A30B268A76ED395 (user_id), INDEX IDX_6A30B2683EB84A1D (magazine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entry ADD CONSTRAINT FK_2B219D70A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE entry ADD CONSTRAINT FK_2B219D703EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id)');
        $this->addSql('ALTER TABLE moderator ADD CONSTRAINT FK_6A30B268A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE moderator ADD CONSTRAINT FK_6A30B2683EB84A1D FOREIGN KEY (magazine_id) REFERENCES magazine (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry DROP FOREIGN KEY FK_2B219D703EB84A1D');
        $this->addSql('ALTER TABLE moderator DROP FOREIGN KEY FK_6A30B2683EB84A1D');
        $this->addSql('ALTER TABLE entry DROP FOREIGN KEY FK_2B219D70A76ED395');
        $this->addSql('ALTER TABLE moderator DROP FOREIGN KEY FK_6A30B268A76ED395');
        $this->addSql('DROP TABLE entry');
        $this->addSql('DROP TABLE magazine');
        $this->addSql('DROP TABLE moderator');
        $this->addSql('DROP TABLE `user`');
    }
}
