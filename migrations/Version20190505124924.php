<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190505124924 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship DROP FOREIGN KEY FK_FA30EB247E3C61F9');
        $this->addSql('DROP INDEX IDX_FA30EB247E3C61F9 ON ship');
        $this->addSql('ALTER TABLE ship DROP owner_id, CHANGE fleet_id fleet_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE raw_data raw_data JSON NOT NULL, CHANGE pledge_date pledge_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE cost cost DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE fleet CHANGE owner_id owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE upload_date upload_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE citizen_id citizen_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE citizen CHANGE organisations organisations JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen CHANGE organisations organisations LONGTEXT NOT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE fleet CHANGE owner_id owner_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE upload_date upload_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ship ADD owner_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE fleet_id fleet_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE raw_data raw_data LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE pledge_date pledge_date DATETIME NOT NULL, CHANGE cost cost DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ship ADD CONSTRAINT FK_FA30EB247E3C61F9 FOREIGN KEY (owner_id) REFERENCES citizen (id)');
        $this->addSql('CREATE INDEX IDX_FA30EB247E3C61F9 ON ship (owner_id)');
        $this->addSql('ALTER TABLE user CHANGE citizen_id citizen_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\'');
    }
}
