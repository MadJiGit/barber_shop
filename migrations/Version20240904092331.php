<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240904092331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, barber_id INT NOT NULL, procedure_type_id INT NOT NULL, date DATETIME NOT NULL, duration INT NOT NULL, date_added DATETIME NOT NULL, date_update DATETIME DEFAULT NULL, date_canceled DATETIME DEFAULT NULL, date_last_update DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6A41727A19EB6921 (client_id), UNIQUE INDEX UNIQ_6A41727ABFF2FEF2 (barber_id), UNIQUE INDEX UNIQ_6A41727A9404667A (procedure_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `procedure` (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, price_master INT NOT NULL, price_junior INT NOT NULL, duration_master INT NOT NULL, duration_junior INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON DEFAULT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, nick_name VARCHAR(50) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, date_added DATETIME NOT NULL, date_banned DATETIME DEFAULT NULL, date_last_update DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727ABFF2FEF2 FOREIGN KEY (barber_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A9404667A FOREIGN KEY (procedure_type_id) REFERENCES `procedure` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A19EB6921');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727ABFF2FEF2');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A9404667A');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE `procedure`');
        $this->addSql('DROP TABLE user');
    }
}
