<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210404155802 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fleets (id UUID NOT NULL, user_id UUID NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F3164526A76ED395 ON fleets (user_id)');
        $this->addSql('COMMENT ON COLUMN fleets.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN fleets.user_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN fleets.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE ships (id UUID NOT NULL, fleet_id UUID DEFAULT NULL, name VARCHAR(32) NOT NULL, image_url VARCHAR(1023) DEFAULT NULL, quantity INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27F71B314B061DF9 ON ships (fleet_id)');
        $this->addSql('COMMENT ON COLUMN ships.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN ships.fleet_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE ships ADD CONSTRAINT FK_27F71B314B061DF9 FOREIGN KEY (fleet_id) REFERENCES fleets (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE ships DROP CONSTRAINT FK_27F71B314B061DF9');
        $this->addSql('DROP TABLE fleets');
        $this->addSql('DROP TABLE ships');
    }
}
