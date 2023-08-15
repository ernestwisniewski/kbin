<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230710060447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE oauth2_user_consent_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE oauth2_access_token (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('CREATE INDEX IDX_454D9673C7440455 ON oauth2_access_token (client)');
        $this->addSql('COMMENT ON COLUMN oauth2_access_token.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_access_token.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE oauth2_authorization_code (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('CREATE INDEX IDX_509FEF5FC7440455 ON oauth2_authorization_code (client)');
        $this->addSql('COMMENT ON COLUMN oauth2_authorization_code.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_authorization_code.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE "oauth2_client" (identifier VARCHAR(32) NOT NULL, name VARCHAR(128) NOT NULL, secret VARCHAR(128) DEFAULT NULL, redirect_uris TEXT DEFAULT NULL, grants TEXT DEFAULT NULL, scopes TEXT DEFAULT NULL, active BOOLEAN NOT NULL, allow_plain_text_pkce BOOLEAN DEFAULT false NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(identifier))');
        $this->addSql('COMMENT ON COLUMN "oauth2_client".redirect_uris IS \'(DC2Type:oauth2_redirect_uri)\'');
        $this->addSql('COMMENT ON COLUMN "oauth2_client".grants IS \'(DC2Type:oauth2_grant)\'');
        $this->addSql('COMMENT ON COLUMN "oauth2_client".scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE oauth2_refresh_token (identifier CHAR(80) NOT NULL, access_token CHAR(80) DEFAULT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))');
        $this->addSql('CREATE INDEX IDX_4DD90732B6A2DD68 ON oauth2_refresh_token (access_token)');
        $this->addSql('COMMENT ON COLUMN oauth2_refresh_token.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE oauth2_user_consent (id INT NOT NULL, user_id INT NOT NULL, client_identifier VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, scopes JSON NOT NULL, ip_address VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C8F05D01A76ED395 ON oauth2_user_consent (user_id)');
        $this->addSql('CREATE INDEX IDX_C8F05D01E77ABE2B ON oauth2_user_consent (client_identifier)');
        $this->addSql('COMMENT ON COLUMN oauth2_user_consent.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_user_consent.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES "oauth2_client" (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES "oauth2_client" (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_user_consent ADD CONSTRAINT FK_C8F05D01A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_user_consent ADD CONSTRAINT FK_C8F05D01E77ABE2B FOREIGN KEY (client_identifier) REFERENCES "oauth2_client" (identifier) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ALTER considered_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN report.considered_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE oauth2_client ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE oauth2_client ADD contact_email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE oauth2_client ADD CONSTRAINT FK_669FF9C9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_669FF9C9A76ED395 ON oauth2_client (user_id)');
        $this->addSql('ALTER TABLE "user" ADD is_bot BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('CREATE SEQUENCE oauth2_client_access_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE oauth2_client_access (id INT NOT NULL, client_id VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D959464019EB6921 ON oauth2_client_access (client_id)');
        $this->addSql('COMMENT ON COLUMN oauth2_client_access.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE oauth2_client_access ADD CONSTRAINT FK_D959464019EB6921 FOREIGN KEY (client_id) REFERENCES "oauth2_client" (identifier) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth2_client ADD created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now()');
        $this->addSql('COMMENT ON COLUMN oauth2_client.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE oauth2_client ALTER COLUMN created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE oauth2_client ADD image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE oauth2_client ADD CONSTRAINT FK_669FF9C93DA5256D FOREIGN KEY (image_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_669FF9C93DA5256D ON oauth2_client (image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "oauth2_client" DROP CONSTRAINT FK_669FF9C93DA5256D');
        $this->addSql('DROP INDEX UNIQ_669FF9C93DA5256D');
        $this->addSql('ALTER TABLE "oauth2_client" DROP image_id');
        $this->addSql('ALTER TABLE "oauth2_client" DROP created_at');
        $this->addSql('DROP SEQUENCE oauth2_client_access_id_seq CASCADE');
        $this->addSql('ALTER TABLE oauth2_client_access DROP CONSTRAINT FK_D959464019EB6921');
        $this->addSql('DROP TABLE oauth2_client_access');
        $this->addSql('ALTER TABLE "user" DROP is_bot');
        $this->addSql('ALTER TABLE "oauth2_client" DROP CONSTRAINT FK_669FF9C9A76ED395');
        $this->addSql('DROP INDEX UNIQ_669FF9C9A76ED395');
        $this->addSql('ALTER TABLE "oauth2_client" DROP user_id');
        $this->addSql('ALTER TABLE "oauth2_client" DROP contact_email');
        $this->addSql('DROP SEQUENCE oauth2_user_consent_id_seq CASCADE');
        $this->addSql('ALTER TABLE oauth2_access_token DROP CONSTRAINT FK_454D9673C7440455');
        $this->addSql('ALTER TABLE oauth2_authorization_code DROP CONSTRAINT FK_509FEF5FC7440455');
        $this->addSql('ALTER TABLE oauth2_refresh_token DROP CONSTRAINT FK_4DD90732B6A2DD68');
        $this->addSql('ALTER TABLE oauth2_user_consent DROP CONSTRAINT FK_C8F05D01A76ED395');
        $this->addSql('ALTER TABLE oauth2_user_consent DROP CONSTRAINT FK_C8F05D01E77ABE2B');
        $this->addSql('DROP TABLE oauth2_access_token');
        $this->addSql('DROP TABLE oauth2_authorization_code');
        $this->addSql('DROP TABLE "oauth2_client"');
        $this->addSql('DROP TABLE oauth2_refresh_token');
        $this->addSql('DROP TABLE oauth2_user_consent');
        $this->addSql('ALTER TABLE report ALTER considered_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN report.considered_at IS NULL');
    }
}
