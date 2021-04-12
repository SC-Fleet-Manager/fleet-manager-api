<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210412224435 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organizations (id UUID NOT NULL, founder_id UUID NOT NULL, name VARCHAR(32) NOT NULL, sid VARCHAR(15) NOT NULL, logo_url VARCHAR(1023) DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_427C1C7F57167AB4 ON organizations (sid)');
        $this->addSql('CREATE INDEX founder_idx ON organizations (founder_id)');
        $this->addSql('COMMENT ON COLUMN organizations.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN organizations.founder_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN organizations.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE organizations');
    }
}
