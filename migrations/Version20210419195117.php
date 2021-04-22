<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210419195117 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organization_fleets (orga_id UUID NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(orga_id))');
        $this->addSql('COMMENT ON COLUMN organization_fleets.orga_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN organization_fleets.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE organization_ship_members (member_id UUID NOT NULL, organization_ship_id UUID NOT NULL, quantity INT NOT NULL, PRIMARY KEY(member_id, organization_ship_id))');
        $this->addSql('CREATE INDEX IDX_8C6A1682F84B93E ON organization_ship_members (organization_ship_id)');
        $this->addSql('COMMENT ON COLUMN organization_ship_members.member_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN organization_ship_members.organization_ship_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE organization_ships (id UUID NOT NULL, organization_fleet_id UUID DEFAULT NULL, model VARCHAR(32) NOT NULL, image_url VARCHAR(1023) DEFAULT NULL, quantity INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A970FDF6A41EDF5B ON organization_ships (organization_fleet_id)');
        $this->addSql('COMMENT ON COLUMN organization_ships.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN organization_ships.organization_fleet_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE organization_ship_members ADD CONSTRAINT FK_8C6A1682F84B93E FOREIGN KEY (organization_ship_id) REFERENCES organization_ships (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_ships ADD CONSTRAINT FK_A970FDF6A41EDF5B FOREIGN KEY (organization_fleet_id) REFERENCES organization_fleets (orga_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE organization_ships DROP CONSTRAINT FK_A970FDF6A41EDF5B');
        $this->addSql('ALTER TABLE organization_ship_members DROP CONSTRAINT FK_8C6A1682F84B93E');
        $this->addSql('DROP TABLE organization_fleets');
        $this->addSql('DROP TABLE organization_ship_members');
        $this->addSql('DROP TABLE organization_ships');
    }
}
