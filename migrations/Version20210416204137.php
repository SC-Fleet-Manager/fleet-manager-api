<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210416204137 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organizations ADD normalized_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE organizations SET normalized_name = name');
        $this->addSql('ALTER TABLE organizations ALTER COLUMN normalized_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE organizations DROP normalized_name');
    }
}
