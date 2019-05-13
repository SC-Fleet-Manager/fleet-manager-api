<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190507185827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add publicChoice field to user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD public_choice VARCHAR(15) DEFAULT \'private\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP public_choice');
    }
}
