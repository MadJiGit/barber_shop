<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial MySQL schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) DEFAULT NULL,
            first_name VARCHAR(50) DEFAULT NULL,
            last_name VARCHAR(50) DEFAULT NULL,
            nick_name VARCHAR(50) DEFAULT NULL,
            phone VARCHAR(30) DEFAULT NULL,
            date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_banned DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_banned TINYINT(1) DEFAULT 0 NOT NULL,
            confirmation_token VARCHAR(255) DEFAULT NULL,
            token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            pending_password VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `procedures` (
            id INT AUTO_INCREMENT NOT NULL,
            type VARCHAR(100) NOT NULL,
            price_master NUMERIC(10, 2) NOT NULL,
            price_junior NUMERIC(10, 2) NOT NULL,
            duration_master INT NOT NULL,
            duration_junior INT NOT NULL,
            available TINYINT(1) DEFAULT 1 NOT NULL,
            date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_stopped DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE appointments (
            id INT AUTO_INCREMENT NOT NULL,
            client_id INT NOT NULL,
            barber_id INT NOT NULL,
            procedure_id INT NOT NULL,
            date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            duration INT NOT NULL,
            status VARCHAR(30) NOT NULL,
            date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_last_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            date_canceled DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            cancellation_reason LONGTEXT DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            confirmation_token VARCHAR(64) DEFAULT NULL,
            confirmed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_6A41727AC05FB297 (confirmation_token),
            INDEX IDX_6A41727A19EB6921 (client_id),
            INDEX IDX_6A41727ABFF2FEF2 (barber_id),
            INDEX IDX_6A41727A1624BCD2 (procedure_id),
            INDEX idx_appointments_date (date),
            INDEX idx_appointments_barber_date (barber_id, date),
            INDEX idx_appointments_client_date (client_id, date),
            INDEX idx_appointments_status (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE barber_procedure (
            id INT AUTO_INCREMENT NOT NULL,
            barber_id INT NOT NULL,
            procedure_id INT NOT NULL,
            can_perform TINYINT(1) DEFAULT 1 NOT NULL,
            valid_from DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            valid_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_E8A4E019BFF2FEF2 (barber_id),
            INDEX IDX_E8A4E0191624BCD2 (procedure_id),
            INDEX idx_barber_procedure (barber_id, procedure_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE barber_schedule (
            id INT AUTO_INCREMENT NOT NULL,
            barber_id INT NOT NULL,
            schedule_data JSON NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_73B68D78BFF2FEF2 (barber_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE barber_schedule_exception (
            id INT AUTO_INCREMENT NOT NULL,
            barber_id INT NOT NULL,
            created_by INT DEFAULT NULL,
            date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            is_available TINYINT(1) DEFAULT 1 NOT NULL,
            start_time TIME DEFAULT NULL,
            end_time TIME DEFAULT NULL,
            excluded_slots JSON DEFAULT NULL,
            reason VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_8DC81892BFF2FEF2 (barber_id),
            INDEX IDX_8DC81892DE12AB56 (created_by),
            INDEX idx_barber_date (barber_id, date),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE business_hours (
            id INT AUTO_INCREMENT NOT NULL,
            day_of_week SMALLINT NOT NULL,
            open_time TIME NOT NULL,
            close_time TIME NOT NULL,
            is_closed TINYINT(1) DEFAULT 0 NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE business_hours_exception (
            id INT AUTO_INCREMENT NOT NULL,
            barber_id INT DEFAULT NULL,
            created_by INT DEFAULT NULL,
            date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            reason VARCHAR(255) NOT NULL,
            is_closed TINYINT(1) DEFAULT 1 NOT NULL,
            open_time TIME DEFAULT NULL,
            close_time TIME DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_8B1FA04BBFF2FEF2 (barber_id),
            INDEX IDX_8B1FA04BDE12AB56 (created_by),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727ABFF2FEF2 FOREIGN KEY (barber_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A1624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedures` (id)');
        $this->addSql('ALTER TABLE barber_procedure ADD CONSTRAINT FK_E8A4E019BFF2FEF2 FOREIGN KEY (barber_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE barber_procedure ADD CONSTRAINT FK_E8A4E0191624BCD2 FOREIGN KEY (procedure_id) REFERENCES `procedures` (id)');
        $this->addSql('ALTER TABLE barber_schedule ADD CONSTRAINT FK_73B68D78BFF2FEF2 FOREIGN KEY (barber_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE barber_schedule_exception ADD CONSTRAINT FK_8DC81892BFF2FEF2 FOREIGN KEY (barber_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE barber_schedule_exception ADD CONSTRAINT FK_8DC81892DE12AB56 FOREIGN KEY (created_by) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE business_hours_exception ADD CONSTRAINT FK_8B1FA04BBFF2FEF2 FOREIGN KEY (barber_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE business_hours_exception ADD CONSTRAINT FK_8B1FA04BDE12AB56 FOREIGN KEY (created_by) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A19EB6921');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727ABFF2FEF2');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A1624BCD2');
        $this->addSql('ALTER TABLE barber_procedure DROP FOREIGN KEY FK_E8A4E019BFF2FEF2');
        $this->addSql('ALTER TABLE barber_procedure DROP FOREIGN KEY FK_E8A4E0191624BCD2');
        $this->addSql('ALTER TABLE barber_schedule DROP FOREIGN KEY FK_73B68D78BFF2FEF2');
        $this->addSql('ALTER TABLE barber_schedule_exception DROP FOREIGN KEY FK_8DC81892BFF2FEF2');
        $this->addSql('ALTER TABLE barber_schedule_exception DROP FOREIGN KEY FK_8DC81892DE12AB56');
        $this->addSql('ALTER TABLE business_hours_exception DROP FOREIGN KEY FK_8B1FA04BBFF2FEF2');
        $this->addSql('ALTER TABLE business_hours_exception DROP FOREIGN KEY FK_8B1FA04BDE12AB56');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE barber_procedure');
        $this->addSql('DROP TABLE barber_schedule');
        $this->addSql('DROP TABLE barber_schedule_exception');
        $this->addSql('DROP TABLE business_hours');
        $this->addSql('DROP TABLE business_hours_exception');
        $this->addSql('DROP TABLE `procedures`');
        $this->addSql('DROP TABLE `user`');
    }
}
