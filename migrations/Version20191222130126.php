<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191222130126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add insurance_duration on ship table and supporter_visible on user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship ADD insurance_duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD supporter_visible TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP supporter_visible');
        $this->addSql('ALTER TABLE ship DROP insurance_duration');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
