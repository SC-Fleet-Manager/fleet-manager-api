<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191222204757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add supporter_visible on organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization ADD supporter_visible TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization DROP supporter_visible');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
