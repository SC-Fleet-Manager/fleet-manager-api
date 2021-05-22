<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210522155300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organization_fleet_member_versions (member_id UUID NOT NULL, organization_fleet_id UUID NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(member_id, organization_fleet_id))');
        $this->addSql('CREATE INDEX IDX_BC393CAA41EDF5B ON organization_fleet_member_versions (organization_fleet_id)');
        $this->addSql('COMMENT ON COLUMN organization_fleet_member_versions.member_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN organization_fleet_member_versions.organization_fleet_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE organization_fleet_member_versions ADD CONSTRAINT FK_BC393CAA41EDF5B FOREIGN KEY (organization_fleet_id) REFERENCES organization_fleets (orga_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE organization_fleet_member_versions');
    }
}
