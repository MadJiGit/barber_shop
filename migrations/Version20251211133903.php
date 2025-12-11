<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211133903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id SERIAL NOT NULL, client_id INT NOT NULL, barber_id INT NOT NULL, procedure_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT NOT NULL, status VARCHAR(30) NOT NULL, date_added TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_last_update TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_canceled TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cancellation_reason TEXT DEFAULT NULL, notes TEXT DEFAULT NULL, confirmation_token VARCHAR(64) DEFAULT NULL, confirmed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A41727AC05FB297 ON appointments (confirmation_token)');
        $this->addSql('CREATE INDEX IDX_6A41727A19EB6921 ON appointments (client_id)');
        $this->addSql('CREATE INDEX IDX_6A41727ABFF2FEF2 ON appointments (barber_id)');
        $this->addSql('CREATE INDEX IDX_6A41727A1624BCD2 ON appointments (procedure_id)');
        $this->addSql('CREATE INDEX idx_appointments_date ON appointments (date)');
        $this->addSql('CREATE INDEX idx_appointments_barber_date ON appointments (barber_id, date)');
        $this->addSql('CREATE INDEX idx_appointments_client_date ON appointments (client_id, date)');
        $this->addSql('CREATE INDEX idx_appointments_status ON appointments (status)');
        $this->addSql('COMMENT ON COLUMN appointments.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_added IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_canceled IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.confirmed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE barber_procedure (id SERIAL NOT NULL, barber_id INT NOT NULL, procedure_id INT NOT NULL, can_perform BOOLEAN DEFAULT true NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E8A4E019BFF2FEF2 ON barber_procedure (barber_id)');
        $this->addSql('CREATE INDEX IDX_E8A4E0191624BCD2 ON barber_procedure (procedure_id)');
        $this->addSql('CREATE INDEX idx_barber_procedure ON barber_procedure (barber_id, procedure_id)');
        $this->addSql('COMMENT ON COLUMN barber_procedure.valid_from IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_procedure.valid_until IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE barber_schedule (id SERIAL NOT NULL, barber_id INT NOT NULL, schedule_data JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73B68D78BFF2FEF2 ON barber_schedule (barber_id)');
        $this->addSql('COMMENT ON COLUMN barber_schedule.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_schedule.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE barber_schedule_exception (id SERIAL NOT NULL, barber_id INT NOT NULL, created_by INT DEFAULT NULL, date DATE NOT NULL, is_available BOOLEAN DEFAULT true NOT NULL, start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, end_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, excluded_slots JSON DEFAULT NULL, reason VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8DC81892BFF2FEF2 ON barber_schedule_exception (barber_id)');
        $this->addSql('CREATE INDEX IDX_8DC81892DE12AB56 ON barber_schedule_exception (created_by)');
        $this->addSql('CREATE INDEX idx_barber_date ON barber_schedule_exception (barber_id, date)');
        $this->addSql('COMMENT ON COLUMN barber_schedule_exception.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_schedule_exception.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE business_hours (id SERIAL NOT NULL, day_of_week SMALLINT NOT NULL, open_time TIME(0) WITHOUT TIME ZONE NOT NULL, close_time TIME(0) WITHOUT TIME ZONE NOT NULL, is_closed BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE business_hours_exception (id SERIAL NOT NULL, barber_id INT DEFAULT NULL, created_by INT DEFAULT NULL, date DATE NOT NULL, reason VARCHAR(255) NOT NULL, is_closed BOOLEAN DEFAULT true NOT NULL, open_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, close_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B1FA04BBFF2FEF2 ON business_hours_exception (barber_id)');
        $this->addSql('CREATE INDEX IDX_8B1FA04BDE12AB56 ON business_hours_exception (created_by)');
        $this->addSql('COMMENT ON COLUMN business_hours_exception.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN business_hours_exception.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "procedures" (id SERIAL NOT NULL, type VARCHAR(100) NOT NULL, price_master NUMERIC(10, 2) NOT NULL, price_junior NUMERIC(10, 2) NOT NULL, duration_master INT NOT NULL, duration_junior INT NOT NULL, available BOOLEAN DEFAULT true NOT NULL, date_added TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_last_update TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_stopped TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "procedures".date_added IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "procedures".date_last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "procedures".date_stopped IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, nick_name VARCHAR(50) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, date_added TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_banned TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_last_update TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_banned BOOLEAN DEFAULT false NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".date_added IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".date_banned IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".date_last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".token_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A19EB6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727ABFF2FEF2 FOREIGN KEY (barber_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A1624BCD2 FOREIGN KEY (procedure_id) REFERENCES "procedures" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE barber_procedure ADD CONSTRAINT FK_E8A4E019BFF2FEF2 FOREIGN KEY (barber_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE barber_procedure ADD CONSTRAINT FK_E8A4E0191624BCD2 FOREIGN KEY (procedure_id) REFERENCES "procedures" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE barber_schedule ADD CONSTRAINT FK_73B68D78BFF2FEF2 FOREIGN KEY (barber_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE barber_schedule_exception ADD CONSTRAINT FK_8DC81892BFF2FEF2 FOREIGN KEY (barber_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE barber_schedule_exception ADD CONSTRAINT FK_8DC81892DE12AB56 FOREIGN KEY (created_by) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_hours_exception ADD CONSTRAINT FK_8B1FA04BBFF2FEF2 FOREIGN KEY (barber_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_hours_exception ADD CONSTRAINT FK_8B1FA04BDE12AB56 FOREIGN KEY (created_by) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE appointments DROP CONSTRAINT FK_6A41727A19EB6921');
        $this->addSql('ALTER TABLE appointments DROP CONSTRAINT FK_6A41727ABFF2FEF2');
        $this->addSql('ALTER TABLE appointments DROP CONSTRAINT FK_6A41727A1624BCD2');
        $this->addSql('ALTER TABLE barber_procedure DROP CONSTRAINT FK_E8A4E019BFF2FEF2');
        $this->addSql('ALTER TABLE barber_procedure DROP CONSTRAINT FK_E8A4E0191624BCD2');
        $this->addSql('ALTER TABLE barber_schedule DROP CONSTRAINT FK_73B68D78BFF2FEF2');
        $this->addSql('ALTER TABLE barber_schedule_exception DROP CONSTRAINT FK_8DC81892BFF2FEF2');
        $this->addSql('ALTER TABLE barber_schedule_exception DROP CONSTRAINT FK_8DC81892DE12AB56');
        $this->addSql('ALTER TABLE business_hours_exception DROP CONSTRAINT FK_8B1FA04BBFF2FEF2');
        $this->addSql('ALTER TABLE business_hours_exception DROP CONSTRAINT FK_8B1FA04BDE12AB56');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE barber_procedure');
        $this->addSql('DROP TABLE barber_schedule');
        $this->addSql('DROP TABLE barber_schedule_exception');
        $this->addSql('DROP TABLE business_hours');
        $this->addSql('DROP TABLE business_hours_exception');
        $this->addSql('DROP TABLE "procedures"');
        $this->addSql('DROP TABLE "user"');
    }
}
