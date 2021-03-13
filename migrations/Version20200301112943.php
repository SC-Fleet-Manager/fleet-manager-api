<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200301112943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add insurance_type on ship table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship ADD insurance_type VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship DROP insurance_type');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
