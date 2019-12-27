<?php

namespace DoctrineMigrations;

use App\Entity\MonthlyCostCoverage;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191208111531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add monthly cost coverage entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE monthly_cost_coverage (
            id CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
            month DATE NOT NULL COMMENT '(DC2Type:date_immutable)',
            target INT NOT NULL,
            postpone TINYINT(1) DEFAULT '1' NOT NULL,
            UNIQUE INDEX UNIQ_2736E028EB61006 (month),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql(sprintf("INSERT INTO monthly_cost_coverage VALUES ('2e08bf25-8d38-43f0-a512-4a1557541d37', '%s', 0, 0)", MonthlyCostCoverage::DEFAULT_DATE));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE monthly_cost_coverage');
    }
}
