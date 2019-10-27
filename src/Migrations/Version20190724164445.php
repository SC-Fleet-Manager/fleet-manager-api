<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190724164445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add registration feature fields to user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user 
            ADD password VARCHAR(127) DEFAULT NULL, 
            ADD registration_confirmation_token CHAR(64) DEFAULT NULL, 
            ADD email VARCHAR(127) DEFAULT NULL, 
            ADD nickname VARCHAR(255) DEFAULT NULL,
            ADD email_confirmed TINYINT(1) DEFAULT \'0\' NOT NULL,
            CHANGE discord_id discord_id VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('UPDATE user SET nickname=username');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user 
            DROP password, 
            DROP registration_confirmation_token, 
            DROP email, 
            DROP nickname,
            DROP email_confirmed,
            CHANGE discord_id discord_id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci
        ');
    }
}
