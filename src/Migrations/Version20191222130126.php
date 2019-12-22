<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191222130126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add insurance duration on ship table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship ADD insurance_duration INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship DROP insurance_duration');
    }
}
