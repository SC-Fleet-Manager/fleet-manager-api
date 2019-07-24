<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190724164445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password to user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD password VARCHAR(127) DEFAULT NULL, CHANGE discord_id discord_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP password, CHANGE discord_id discord_id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
