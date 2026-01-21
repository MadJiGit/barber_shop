<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121075228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments ALTER date TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER date_added TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER date_last_update TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER date_canceled TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER confirmed_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN appointments.date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_added IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_last_update IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_canceled IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.confirmed_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE barber_procedure ALTER valid_from TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE barber_procedure ALTER valid_until TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN barber_procedure.valid_from IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_procedure.valid_until IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE barber_schedule ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE barber_schedule ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN barber_schedule.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_schedule.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE barber_schedule_exception ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN barber_schedule_exception.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE business_hours_exception ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN business_hours_exception.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE procedures ALTER date_added TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE procedures ALTER date_last_update TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE procedures ALTER date_stopped TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN procedures.date_added IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN procedures.date_last_update IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN procedures.date_stopped IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE "user" ADD pending_password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER date_added TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER date_banned TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER date_last_update TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER token_expires_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".date_added IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".date_banned IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".date_last_update IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".token_expires_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "procedures" ALTER date_added TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "procedures" ALTER date_last_update TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "procedures" ALTER date_stopped TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "procedures".date_added IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "procedures".date_last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "procedures".date_stopped IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "user" DROP pending_password');
        $this->addSql('ALTER TABLE "user" ALTER date_added TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER date_banned TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER date_last_update TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER token_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".date_added IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".date_banned IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".date_last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".token_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE business_hours_exception ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN business_hours_exception.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE barber_procedure ALTER valid_from TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE barber_procedure ALTER valid_until TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN barber_procedure.valid_from IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_procedure.valid_until IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE barber_schedule_exception ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN barber_schedule_exception.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE barber_schedule ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE barber_schedule ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN barber_schedule.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN barber_schedule.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE appointments ALTER date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER date_added TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER date_last_update TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER date_canceled TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER confirmed_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN appointments.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_added IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.date_canceled IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointments.confirmed_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
