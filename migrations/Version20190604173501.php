<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190604173501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nickname field in citizen table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen ADD nickname VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen DROP nickname');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
