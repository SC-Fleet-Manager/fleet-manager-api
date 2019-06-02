<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190531191345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add citizen_organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE citizen_organization (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', citizen_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', organization_sid VARCHAR(31) NOT NULL, rank SMALLINT NOT NULL, rank_name VARCHAR(31) DEFAULT NULL, INDEX IDX_63CFDE85A63C3C2E (citizen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_organization ADD CONSTRAINT FK_63CFDE85A63C3C2E FOREIGN KEY (citizen_id) REFERENCES citizen (id)');
        $this->addSql('DROP INDEX mainorga_idx ON citizen');
        $this->addSql('ALTER TABLE citizen ADD last_fleet_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD main_orga_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', DROP main_orga');
        $this->addSql('ALTER TABLE citizen ADD CONSTRAINT FK_A953172933675867 FOREIGN KEY (last_fleet_id) REFERENCES fleet (id)');
        $this->addSql('ALTER TABLE citizen ADD CONSTRAINT FK_A953172963BBC148 FOREIGN KEY (main_orga_id) REFERENCES citizen_organization (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A953172933675867 ON citizen (last_fleet_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A953172963BBC148 ON citizen (main_orga_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen DROP FOREIGN KEY FK_A953172963BBC148');
        $this->addSql('DROP TABLE citizen_organization');
        $this->addSql('ALTER TABLE citizen DROP FOREIGN KEY FK_A953172933675867');
        $this->addSql('DROP INDEX UNIQ_A953172933675867 ON citizen');
        $this->addSql('DROP INDEX UNIQ_A953172963BBC148 ON citizen');
        $this->addSql('ALTER TABLE citizen ADD main_orga VARCHAR(31) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP last_fleet_id, DROP main_orga_id');
        $this->addSql('CREATE INDEX mainorga_idx ON citizen (main_orga)');
    }
}
