<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190531203028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organization (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', organization_sid VARCHAR(31) NOT NULL, name VARCHAR(255) DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, public_choice VARCHAR(15) DEFAULT \'private\' NOT NULL, UNIQUE INDEX UNIQ_C1EE637C5D9B2557 (organization_sid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_organization CHANGE rank rank SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE organization');
        $this->addSql('ALTER TABLE citizen_organization CHANGE rank rank SMALLINT NOT NULL');
    }
}
