<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210506210323 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ship_templates (id UUID NOT NULL, author_id UUID NOT NULL, model VARCHAR(60) NOT NULL, image_url VARCHAR(1023) DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, version INT DEFAULT 1 NOT NULL, chassis_name VARCHAR(60) NOT NULL, manufacturer_name VARCHAR(50) DEFAULT NULL, manufacturer_code VARCHAR(5) DEFAULT NULL, ship_size_size VARCHAR(10) DEFAULT NULL, ship_role_role VARCHAR(30) DEFAULT NULL, cargo_capacity_capacity INT NOT NULL, crew_min NUMERIC(10, 0) DEFAULT NULL, crew_max NUMERIC(10, 0) DEFAULT NULL, price_pledge NUMERIC(12, 2) DEFAULT NULL, price_ingame NUMERIC(12, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN ship_templates.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN ship_templates.author_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN ship_templates.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE ship_templates');
    }
}
