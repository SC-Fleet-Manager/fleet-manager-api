<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190612170842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add refresh_date field in fleet table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE fleet ADD refresh_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fleet DROP refresh_date');
    }
}
