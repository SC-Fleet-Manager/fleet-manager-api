<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190620173653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table organization_change.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organization_change (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', organization_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', author_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(31) NOT NULL, payload JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', INDEX IDX_45D92ED532C8A3DE (organization_id), INDEX IDX_45D92ED5F675F31B (author_id), INDEX type_idx (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE organization_change ADD CONSTRAINT FK_45D92ED532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE organization_change ADD CONSTRAINT FK_45D92ED5F675F31B FOREIGN KEY (author_id) REFERENCES citizen (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE organization_change');
    }
}
