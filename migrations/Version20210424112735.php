<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210424112735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE memberships DROP CONSTRAINT FK_865A477632C8A3DE');
        $this->addSql('ALTER TABLE memberships ADD CONSTRAINT FK_865A477632C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_ship_members DROP CONSTRAINT FK_8C6A1682F84B93E');
        $this->addSql('ALTER TABLE organization_ship_members ADD CONSTRAINT FK_8C6A1682F84B93E FOREIGN KEY (organization_ship_id) REFERENCES organization_ships (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_ships DROP CONSTRAINT FK_A970FDF6A41EDF5B');
        $this->addSql('ALTER TABLE organization_ships ADD CONSTRAINT FK_A970FDF6A41EDF5B FOREIGN KEY (organization_fleet_id) REFERENCES organization_fleets (orga_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE memberships DROP CONSTRAINT fk_865a477632c8a3de');
        $this->addSql('ALTER TABLE memberships ADD CONSTRAINT fk_865a477632c8a3de FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_ships DROP CONSTRAINT fk_a970fdf6a41edf5b');
        $this->addSql('ALTER TABLE organization_ships ADD CONSTRAINT fk_a970fdf6a41edf5b FOREIGN KEY (organization_fleet_id) REFERENCES organization_fleets (orga_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_ship_members DROP CONSTRAINT fk_8c6a1682f84b93e');
        $this->addSql('ALTER TABLE organization_ship_members ADD CONSTRAINT fk_8c6a1682f84b93e FOREIGN KEY (organization_ship_id) REFERENCES organization_ships (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
