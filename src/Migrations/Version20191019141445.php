<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191019141445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ON DELETE SET NULL on citizen.lastFleet, citizen.mainOrga, user.citizen and orga_change.citizen';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen DROP FOREIGN KEY FK_A953172933675867');
        $this->addSql('ALTER TABLE citizen DROP FOREIGN KEY FK_A953172963BBC148');
        $this->addSql('ALTER TABLE citizen ADD CONSTRAINT FK_A953172933675867 FOREIGN KEY (last_fleet_id) REFERENCES fleet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE citizen ADD CONSTRAINT FK_A953172963BBC148 FOREIGN KEY (main_orga_id) REFERENCES citizen_organization (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A63C3C2E');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A63C3C2E FOREIGN KEY (citizen_id) REFERENCES citizen (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE organization_change DROP FOREIGN KEY FK_45D92ED5F675F31B');
        $this->addSql('ALTER TABLE organization_change ADD CONSTRAINT FK_45D92ED5F675F31B FOREIGN KEY (author_id) REFERENCES citizen (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen DROP FOREIGN KEY FK_A953172933675867');
        $this->addSql('ALTER TABLE citizen DROP FOREIGN KEY FK_A953172963BBC148');
        $this->addSql('ALTER TABLE citizen ADD CONSTRAINT FK_A953172933675867 FOREIGN KEY (last_fleet_id) REFERENCES fleet (id)');
        $this->addSql('ALTER TABLE citizen ADD CONSTRAINT FK_A953172963BBC148 FOREIGN KEY (main_orga_id) REFERENCES citizen_organization (id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A63C3C2E');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A63C3C2E FOREIGN KEY (citizen_id) REFERENCES citizen (id)');
        $this->addSql('ALTER TABLE organization_change DROP FOREIGN KEY FK_45D92ED5F675F31B');
        $this->addSql('ALTER TABLE organization_change ADD CONSTRAINT FK_45D92ED5F675F31B FOREIGN KEY (author_id) REFERENCES citizen (id)');
    }
}
