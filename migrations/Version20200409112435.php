<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200409112435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add normalized ship name.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ship_name ADD provider_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD my_hangar_name_pattern VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ship ADD normalized_name VARCHAR(255) DEFAULT NULL, ADD galaxy_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql("UPDATE ship_name SET my_hangar_name_pattern = CONCAT('^', ship_matrix_name, '$')");

        $this->addSql('CREATE INDEX my_hangar_name_pattern_idx ON ship_name (my_hangar_name_pattern)');
        $this->addSql('CREATE INDEX provider_id_idx ON ship_name (provider_id)');
        $this->addSql('CREATE INDEX galaxy_id_idx ON ship (galaxy_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX galaxy_id_idx ON ship');
        $this->addSql('DROP INDEX my_hangar_name_pattern_idx ON ship_name');
        $this->addSql('DROP INDEX provider_id_idx ON ship_name');
        $this->addSql('ALTER TABLE ship DROP normalized_name, DROP galaxy_id');
        $this->addSql('ALTER TABLE ship_name DROP provider_id, DROP my_hangar_name_pattern');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
