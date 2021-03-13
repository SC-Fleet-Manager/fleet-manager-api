<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190520175827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index to name field on Ship table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship CHANGE raw_data raw_data JSON NOT NULL');
        $this->addSql('CREATE INDEX name_idx ON ship (name)');
        $this->addSql('ALTER TABLE citizen CHANGE organisations organisations JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen CHANGE organisations organisations LONGTEXT NOT NULL COLLATE utf8mb4_bin');
        $this->addSql('DROP INDEX name_idx ON ship');
        $this->addSql('ALTER TABLE ship CHANGE raw_data raw_data LONGTEXT NOT NULL COLLATE utf8mb4_bin');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
