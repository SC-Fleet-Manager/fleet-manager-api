<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190528185409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add main_orga field on citizen table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen ADD main_orga VARCHAR(31) DEFAULT NULL');
        $this->addSql('CREATE INDEX actualhandle_idx ON citizen (actual_handle)');
        $this->addSql('CREATE INDEX mainorga_idx ON citizen (main_orga)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX actualhandle_idx ON citizen');
        $this->addSql('DROP INDEX mainorga_idx ON citizen');
        $this->addSql('ALTER TABLE citizen DROP main_orga');
    }
}
