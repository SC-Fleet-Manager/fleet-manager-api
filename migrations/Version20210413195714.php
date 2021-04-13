<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210413195714 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE memberships (member_id UUID NOT NULL, organization_id UUID NOT NULL, joined BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(member_id, organization_id))');
        $this->addSql('CREATE INDEX IDX_865A477632C8A3DE ON memberships (organization_id)');
        $this->addSql('COMMENT ON COLUMN memberships.member_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN memberships.organization_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE memberships ADD CONSTRAINT FK_865A477632C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE memberships');
    }
}
