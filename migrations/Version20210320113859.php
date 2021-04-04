<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210320113859 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE funding (id UUID NOT NULL, user_id UUID DEFAULT NULL, gateway VARCHAR(15) NOT NULL, paypal_order_id VARCHAR(31) DEFAULT NULL, paypal_status VARCHAR(31) DEFAULT NULL, paypal_purchase JSON DEFAULT NULL, amount NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, net_amount INT DEFAULT 0 NOT NULL, currency CHAR(3) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, refunded_amount INT DEFAULT 0 NOT NULL, refunded_net_amount INT DEFAULT 0 NOT NULL, refunded_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D30DD1D6A76ED395 ON funding (user_id)');
        $this->addSql('CREATE INDEX funding_paypal_order_id_idx ON funding (paypal_order_id)');
        $this->addSql('CREATE INDEX funding_created_at_idx ON funding (created_at)');
        $this->addSql('COMMENT ON COLUMN funding.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN funding.user_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN funding.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN funding.refunded_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE monthly_cost_coverage (id UUID NOT NULL, month DATE NOT NULL, target INT NOT NULL, postpone BOOLEAN DEFAULT \'true\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2736E028EB61006 ON monthly_cost_coverage (month)');
        $this->addSql('COMMENT ON COLUMN monthly_cost_coverage.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN monthly_cost_coverage.month IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE patch_note (id UUID NOT NULL, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, link VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX patch_note_created_at_idx ON patch_note (created_at)');
        $this->addSql('COMMENT ON COLUMN patch_note.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN patch_note.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, roles JSON NOT NULL, auth0_username VARCHAR(127) NOT NULL, supporter_visible BOOLEAN DEFAULT \'true\' NOT NULL, coins INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, last_patch_note_read_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F28DC078 ON users (auth0_username)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.last_patch_note_read_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE funding ADD CONSTRAINT FK_D30DD1D6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE funding DROP CONSTRAINT FK_D30DD1D6A76ED395');
        $this->addSql('DROP TABLE funding');
        $this->addSql('DROP TABLE monthly_cost_coverage');
        $this->addSql('DROP TABLE patch_note');
        $this->addSql('DROP TABLE users');
    }
}
