<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191227183954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ship chassis Rover.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO ship_chassis(id, rsi_id, name) VALUES('9bc30be9-0f1e-405a-a730-0d244292b6e5', 1002, 'Rover');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM ship_chassis WHERE id='9bc30be9-0f1e-405a-a730-0d244292b6e5'");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
