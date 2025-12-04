<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126083550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, barber_id INT NOT NULL, procedure_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', duration INT NOT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_canceled DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', cancellation_reason LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_6A41727A19EB6921 (client_id), INDEX IDX_6A41727ABFF2FEF2 (barber_id), INDEX IDX_6A41727A1624BCD2 (procedure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `procedure` (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, price_master NUMERIC(10, 2) NOT NULL, price_junior NUMERIC(10, 2) NOT NULL, duration_master INT NOT NULL, duration_junior INT NOT NULL, available TINYINT(1) DEFAULT 1 NOT NULL, date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_stopped DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, nick_name VARCHAR(50) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_banned DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, is_banned TINYINT(1) DEFAULT 0 NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727ABFF2FEF2 FOREIGN KEY (barber_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A1624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedure` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A19EB6921');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727ABFF2FEF2');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A1624BCD2');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE `procedure`');
        $this->addSql('DROP TABLE user');
    }
}
