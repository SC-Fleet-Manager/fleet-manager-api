<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200301151421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove username in user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX username_idx ON user');
        $this->addSql('ALTER TABLE user DROP username, CHANGE roles roles JSON NOT NULL');
        $this->addSql('CREATE INDEX nickname_idx ON user (nickname)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX nickname_idx ON user');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('CREATE INDEX username_idx ON user (username)');
        $this->addSql('UPDATE user SET username=nickname WHERE nickname is not null');
        $this->addSql('UPDATE user SET username=substring(email, 1, locate(\'@\', email) - 1) WHERE email is not null');
        $this->addSql('UPDATE user SET username=id WHERE username is null');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
