<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211135159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seeds initial users (4 barbers/admins) and procedures (16 services)';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;

        // Check if user table is empty
        $userCount = $connection->fetchOne('SELECT COUNT(*) FROM "user"');

        if ($userCount == 0) {
            // Insert users with hashed password (password: 12345678)
            $hashedPassword = '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS';

            // Insert each user separately to avoid ID generation issues
            $connection->executeStatement('
                INSERT INTO "user" (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
                VALUES (:email, :roles, :password, :first_name, :last_name, :nick_name, :phone, NOW(), true, false)
            ', [
                'email' => 'reg9643@gmail.com',
                'roles' => '["ROLE_SUPER_ADMIN"]',
                'password' => $hashedPassword,
                'first_name' => 'Super',
                'last_name' => 'Adminev',
                'nick_name' => 'Super Admina',
                'phone' => '0888888811',
            ]);

            $connection->executeStatement('
                INSERT INTO "user" (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
                VALUES (:email, :roles, :password, :first_name, :last_name, :nick_name, :phone, NOW(), true, false)
            ', [
                'email' => 'admin@abv.bg',
                'roles' => '["ROLE_ADMIN"]',
                'password' => $hashedPassword,
                'first_name' => 'Admin',
                'last_name' => 'Adminev',
                'nick_name' => 'Admina',
                'phone' => '0888888811',
            ]);

            $connection->executeStatement('
                INSERT INTO "user" (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
                VALUES (:email, :roles, :password, :first_name, :last_name, :nick_name, :phone, NOW(), true, false)
            ', [
                'email' => 'barber_senior@abv.bg',
                'roles' => '["ROLE_BARBER_SENIOR"]',
                'password' => $hashedPassword,
                'first_name' => 'Senior',
                'last_name' => 'Barber',
                'nick_name' => 'Seniora',
                'phone' => '0888888888',
            ]);

            $connection->executeStatement('
                INSERT INTO "user" (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
                VALUES (:email, :roles, :password, :first_name, :last_name, :nick_name, :phone, NOW(), true, false)
            ', [
                'email' => 'barber@abv.bg',
                'roles' => '["ROLE_BARBER"]',
                'password' => $hashedPassword,
                'first_name' => 'Barber',
                'last_name' => 'Barber',
                'nick_name' => 'Barbara',
                'phone' => '0999999999',
            ]);

            $this->write('Inserted 4 users (Super Admin, Admin, Senior Barber, Barber)');
        } else {
            $this->write('User table not empty - skipping user seed data');
        }

        // Check if procedures table is empty
        $procedureCount = $connection->fetchOne('SELECT COUNT(*) FROM procedures');

        if ($procedureCount == 0) {
            // Insert procedures
            $connection->executeStatement("
                INSERT INTO procedures (type, price_master, price_junior, duration_master, duration_junior, available, date_added) VALUES
                -- Haircuts
                ('Haircut - Men', 25.00, 20.00, 30, 40, true, NOW()),
                ('Haircut - Kids', 15.00, 12.00, 20, 25, true, NOW()),
                ('Haircut - Senior', 20.00, 18.00, 30, 40, true, NOW()),

                -- Beard Services
                ('Beard Trim', 15.00, 12.00, 20, 25, true, NOW()),
                ('Beard Shaping', 18.00, 15.00, 25, 30, true, NOW()),
                ('Hot Towel Shave', 30.00, 25.00, 40, 50, true, NOW()),

                -- Combo Services
                ('Haircut + Beard Trim', 35.00, 28.00, 45, 60, true, NOW()),
                ('Haircut + Beard Shaping', 38.00, 32.00, 50, 65, true, NOW()),
                ('Haircut + Hot Towel Shave', 50.00, 42.00, 60, 75, true, NOW()),

                -- Premium Services
                ('Premium Haircut + Styling', 40.00, 35.00, 45, 55, false, NOW()),
                ('Head Massage', 20.00, 15.00, 20, 25, false, NOW()),
                ('Hair Coloring', 50.00, 45.00, 90, 120, false, NOW()),
                ('Highlights', 60.00, 55.00, 120, 150, false, NOW()),

                -- Special Services
                ('Wedding/Event Styling', 80.00, 70.00, 90, 120, false, NOW()),
                ('Consultation', 0.00, 0.00, 15, 15, false, NOW())
            ");

            $this->write('Inserted 16 procedures');
        } else {
            $this->write('Procedures table not empty - skipping procedure seed data');
        }
    }

    public function down(Schema $schema): void
    {
        // This migration only inserts data, down() is not needed
        // Manual cleanup required if necessary
        $this->write('Seed data migration - manual cleanup required');
    }
}
