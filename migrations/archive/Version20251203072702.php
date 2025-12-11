<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203072702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Table already renamed manually, just add missing foreign keys
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A1624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedures` (id)');
        $this->addSql('ALTER TABLE barber_procedure ADD CONSTRAINT FK_E8A4E0191624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedures` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A1624BCD2');
        $this->addSql('ALTER TABLE barber_procedure DROP FOREIGN KEY FK_E8A4E0191624BCD2');
        $this->addSql('CREATE TABLE `procedure` (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, price_master NUMERIC(10, 2) NOT NULL, price_junior NUMERIC(10, 2) NOT NULL, duration_master INT NOT NULL, duration_junior INT NOT NULL, available TINYINT(1) DEFAULT 1 NOT NULL, date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_stopped DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE `procedures`');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A1624BCD2');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A1624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedure` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE barber_procedure DROP FOREIGN KEY FK_E8A4E0191624BCD2');
        $this->addSql('ALTER TABLE barber_procedure ADD CONSTRAINT FK_E8A4E0191624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedure` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
