<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190526161634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_refresh field in citizen table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen ADD last_refresh DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen DROP last_refresh');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
