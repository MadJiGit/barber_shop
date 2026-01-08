<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to convert all TIMESTAMP columns to TIMESTAMPTZ (timestamp with time zone).
 * Existing data is treated as UTC and will be properly timezone-aware after migration.
 */
final class Version20251217083843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert all TIMESTAMP columns to TIMESTAMPTZ for proper timezone handling';
    }

    public function up(Schema $schema): void
    {
        // Appointments table (5 columns)
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date TYPE TIMESTAMP(0) WITH TIME ZONE USING date AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date_added TYPE TIMESTAMP(0) WITH TIME ZONE USING date_added AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date_last_update TYPE TIMESTAMP(0) WITH TIME ZONE USING date_last_update AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date_canceled TYPE TIMESTAMP(0) WITH TIME ZONE USING date_canceled AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN confirmed_at TYPE TIMESTAMP(0) WITH TIME ZONE USING confirmed_at AT TIME ZONE \'Europe/Sofia\'');

        // User table (4 columns)
        $this->addSql('ALTER TABLE "user" ALTER COLUMN date_added TYPE TIMESTAMP(0) WITH TIME ZONE USING date_added AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN date_banned TYPE TIMESTAMP(0) WITH TIME ZONE USING date_banned AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN date_last_update TYPE TIMESTAMP(0) WITH TIME ZONE USING date_last_update AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN token_expires_at TYPE TIMESTAMP(0) WITH TIME ZONE USING token_expires_at AT TIME ZONE \'Europe/Sofia\'');

        // Procedures table (3 columns)
        $this->addSql('ALTER TABLE procedures ALTER COLUMN date_added TYPE TIMESTAMP(0) WITH TIME ZONE USING date_added AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE procedures ALTER COLUMN date_last_update TYPE TIMESTAMP(0) WITH TIME ZONE USING date_last_update AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE procedures ALTER COLUMN date_stopped TYPE TIMESTAMP(0) WITH TIME ZONE USING date_stopped AT TIME ZONE \'Europe/Sofia\'');

        // Barber schedule table (2 columns)
        $this->addSql('ALTER TABLE barber_schedule ALTER COLUMN created_at TYPE TIMESTAMP(0) WITH TIME ZONE USING created_at AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE barber_schedule ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITH TIME ZONE USING updated_at AT TIME ZONE \'Europe/Sofia\'');

        // Barber schedule exception table (1 column)
        $this->addSql('ALTER TABLE barber_schedule_exception ALTER COLUMN created_at TYPE TIMESTAMP(0) WITH TIME ZONE USING created_at AT TIME ZONE \'Europe/Sofia\'');

        // Barber procedure table (2 columns)
        $this->addSql('ALTER TABLE barber_procedure ALTER COLUMN valid_from TYPE TIMESTAMP(0) WITH TIME ZONE USING valid_from AT TIME ZONE \'Europe/Sofia\'');
        $this->addSql('ALTER TABLE barber_procedure ALTER COLUMN valid_until TYPE TIMESTAMP(0) WITH TIME ZONE USING valid_until AT TIME ZONE \'Europe/Sofia\'');

        // Business hours exception table (1 column)
        $this->addSql('ALTER TABLE business_hours_exception ALTER COLUMN created_at TYPE TIMESTAMP(0) WITH TIME ZONE USING created_at AT TIME ZONE \'Europe/Sofia\'');
    }

    public function down(Schema $schema): void
    {
        // Rollback: Convert TIMESTAMPTZ back to TIMESTAMP

        // Appointments table
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date_added TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date_last_update TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN date_canceled TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE appointments ALTER COLUMN confirmed_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        // User table
        $this->addSql('ALTER TABLE "user" ALTER COLUMN date_added TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN date_banned TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN date_last_update TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN token_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        // Procedures table
        $this->addSql('ALTER TABLE procedures ALTER COLUMN date_added TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE procedures ALTER COLUMN date_last_update TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE procedures ALTER COLUMN date_stopped TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        // Barber schedule table
        $this->addSql('ALTER TABLE barber_schedule ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE barber_schedule ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        // Barber schedule exception table
        $this->addSql('ALTER TABLE barber_schedule_exception ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        // Barber procedure table
        $this->addSql('ALTER TABLE barber_procedure ALTER COLUMN valid_from TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE barber_procedure ALTER COLUMN valid_until TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        // Business hours exception table
        $this->addSql('ALTER TABLE business_hours_exception ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }
}
