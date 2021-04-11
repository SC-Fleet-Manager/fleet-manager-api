<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210410225927 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX fleetid_name_idx');
        $this->addSql('ALTER TABLE ships RENAME COLUMN name TO model');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE ships RENAME COLUMN model TO name');
        $this->addSql('CREATE UNIQUE INDEX fleetid_name_idx ON ships (fleet_id, name)');
    }
}
