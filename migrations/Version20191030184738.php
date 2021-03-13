<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191030184738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add roles to user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user ADD roles JSON NOT NULL COMMENT '(DC2Type:json_array)'");
        $this->addSql('UPDATE user SET roles=\'["ROLE_USER"]\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP roles');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
