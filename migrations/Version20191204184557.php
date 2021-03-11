<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191204184557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add funding entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE funding (
            id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
            user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
            gateway VARCHAR(15) NOT NULL,
            paypal_order_id VARCHAR(31) DEFAULT NULL,
            paypal_status VARCHAR(31) DEFAULT NULL,
            paypal_purchase JSON DEFAULT NULL,
            amount INT DEFAULT 0 NOT NULL,
            net_amount INT DEFAULT 0 NOT NULL,
            currency CHAR(3) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\',
            refunded_amount INT DEFAULT 0 NOT NULL,
            refunded_net_amount INT DEFAULT 0 NOT NULL,
            refunded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE funding ADD CONSTRAINT FK_D30DD1D6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D30DD1D6A76ED395 ON funding (user_id)');
        $this->addSql('CREATE INDEX paypal_order_id_idx ON funding (paypal_order_id)');
        $this->addSql('CREATE INDEX created_at_idx ON funding (created_at)');
        $this->addSql('ALTER TABLE user ADD coins INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX paypal_order_id_idx ON funding');
        $this->addSql('DROP INDEX created_at_idx ON funding');
        $this->addSql('ALTER TABLE funding DROP FOREIGN KEY FK_D30DD1D6A76ED395');
        $this->addSql('DROP INDEX IDX_D30DD1D6A76ED395 ON funding');
        $this->addSql('DROP TABLE funding');
        $this->addSql('ALTER TABLE user DROP coins');
    }
}
