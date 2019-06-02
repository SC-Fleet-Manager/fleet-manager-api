<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190602084848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index to discord_id field in user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX discord_idx ON user (discord_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX discord_idx ON user');
    }
}
