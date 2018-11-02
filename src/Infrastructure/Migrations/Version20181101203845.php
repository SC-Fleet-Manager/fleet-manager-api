<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181101203845 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fleet (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', upload_date DATETIME NOT NULL, version INT NOT NULL, INDEX IDX_A05E1E477E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ship (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', fleet_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', raw_data JSON NOT NULL, name VARCHAR(255) NOT NULL, manufacturer VARCHAR(255) NOT NULL, pledge_date DATETIME NOT NULL, cost DOUBLE PRECISION NOT NULL, insured TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_FA30EB247E3C61F9 (owner_id), INDEX IDX_FA30EB244B061DF9 (fleet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', number VARCHAR(255) NOT NULL, actual_handle VARCHAR(255) NOT NULL, organisations JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E477E3C61F9 FOREIGN KEY (owner_id) REFERENCES citizen (id)');
        $this->addSql('ALTER TABLE ship ADD CONSTRAINT FK_FA30EB247E3C61F9 FOREIGN KEY (owner_id) REFERENCES citizen (id)');
        $this->addSql('ALTER TABLE ship ADD CONSTRAINT FK_FA30EB244B061DF9 FOREIGN KEY (fleet_id) REFERENCES fleet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ship DROP FOREIGN KEY FK_FA30EB244B061DF9');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E477E3C61F9');
        $this->addSql('ALTER TABLE ship DROP FOREIGN KEY FK_FA30EB247E3C61F9');
        $this->addSql('DROP TABLE fleet');
        $this->addSql('DROP TABLE ship');
        $this->addSql('DROP TABLE citizen');
    }
}
