<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191015185640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add change email.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user ADD pending_email VARCHAR(127) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimetz_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP pending_email, DROP updated_at');
    }
}
