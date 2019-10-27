<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190802162716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lost password fields in user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user 
            ADD lost_password_token CHAR(64) DEFAULT NULL, 
            ADD lost_password_requested_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetimetz_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP lost_password_token, DROP lost_password_requested_at');
    }
}
