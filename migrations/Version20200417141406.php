<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200417141406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add patch note table + last_patch_note_read_at field.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE patch_note (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, link VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE INDEX created_at_idx ON patch_note (created_at)');
        $this->addSql('ALTER TABLE user ADD last_patch_note_read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP last_patch_note_read_at');
        $this->addSql('DROP INDEX created_at_idx ON patch_note');
        $this->addSql('DROP TABLE patch_note');
    }
}
