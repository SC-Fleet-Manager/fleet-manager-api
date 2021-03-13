<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181222193804 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', citizen_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', username VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649A63C3C2E (citizen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A63C3C2E FOREIGN KEY (citizen_id) REFERENCES citizen (id)');
        $this->addSql('ALTER TABLE fleet CHANGE owner_id owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ship CHANGE owner_id owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE fleet_id fleet_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE raw_data raw_data JSON NOT NULL');
        $this->addSql('ALTER TABLE citizen ADD bio LONGTEXT DEFAULT NULL, CHANGE organisations organisations JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE citizen DROP bio, CHANGE organisations organisations LONGTEXT NOT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE fleet CHANGE owner_id owner_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ship CHANGE owner_id owner_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE fleet_id fleet_id CHAR(36) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE raw_data raw_data LONGTEXT NOT NULL COLLATE utf8mb4_bin');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
