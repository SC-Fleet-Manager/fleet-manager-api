<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190607202117 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen CHANGE nickname nickname VARCHAR(255) DEFAULT NULL, ADD avatar_url VARCHAR(255) DEFAULT NULL, ADD count_redacted_organizations INT NOT NULL, ADD redacted_main_orga TINYINT(1) NOT NULL, DROP organisations');
        $this->addSql('ALTER TABLE citizen_organization ADD organization_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE citizen_organization ADD CONSTRAINT FK_63CFDE8532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('CREATE INDEX IDX_63CFDE8532C8A3DE ON citizen_organization (organization_id)');
        $this->addSql('ALTER TABLE user ADD discord_tag VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen ADD organisations JSON NOT NULL, CHANGE nickname nickname VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP avatar_url, DROP count_redacted_organizations, DROP redacted_main_orga');
        $this->addSql('ALTER TABLE citizen_organization DROP FOREIGN KEY FK_63CFDE8532C8A3DE');
        $this->addSql('DROP INDEX IDX_63CFDE8532C8A3DE ON citizen_organization');
        $this->addSql('ALTER TABLE citizen_organization DROP organization_id');
        $this->addSql('ALTER TABLE user DROP discord_tag');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
