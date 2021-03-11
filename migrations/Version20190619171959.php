<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190619171959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_refresh field in organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE organization ADD last_refresh DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimetz_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization DROP last_refresh');
    }
}
