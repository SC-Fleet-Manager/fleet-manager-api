<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191019104318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pending discord id.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD pending_discord_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX username_idx ON user (username)');
        $this->addSql('CREATE INDEX email_idx ON user (email)');
        $this->addSql('OPTIMIZE TABLE user');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX username_idx ON user');
        $this->addSql('DROP INDEX email_idx ON user');
        $this->addSql('ALTER TABLE user DROP pending_discord_id');
    }
}
