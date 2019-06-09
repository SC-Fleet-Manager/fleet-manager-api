<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190608202408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add visibility field in citizen_organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE citizen_organization ADD visibility VARCHAR(15) DEFAULT 'orga' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_organization DROP visibility');
    }
}
