<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211095749 extends AbstractMigration
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

            $connection->executeStatement('
                INSERT INTO "user" (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
                VALUES
                (:email1, :roles1, :password1, :first_name1, :last_name1, :nick_name1, :phone1, NOW(), true, false),
                (:email2, :roles2, :password2, :first_name2, :last_name2, :nick_name2, :phone2, NOW(), true, false),
                (:email3, :roles3, :password3, :first_name3, :last_name3, :nick_name3, :phone3, NOW(), true, false),
                (:email4, :roles4, :password4, :first_name4, :last_name4, :nick_name4, :phone4, NOW(), true, false)
            ', [
                'email1' => 'reg9643@gmail.com',
                'roles1' => '["ROLE_SUPER_ADMIN"]',
                'password1' => $hashedPassword,
                'first_name1' => 'Super',
                'last_name1' => 'Adminev',
                'nick_name1' => 'Super Admina',
                'phone1' => '0888888811',

                'email2' => 'admin@abv.bg',
                'roles2' => '["ROLE_ADMIN"]',
                'password2' => $hashedPassword,
                'first_name2' => 'Admin',
                'last_name2' => 'Adminev',
                'nick_name2' => 'Admina',
                'phone2' => '0888888811',

                'email3' => 'barber_senior@abv.bg',
                'roles3' => '["ROLE_BARBER_SENIOR"]',
                'password3' => $hashedPassword,
                'first_name3' => 'Senior',
                'last_name3' => 'Barber',
                'nick_name3' => 'Seniora',
                'phone3' => '0888888888',

                'email4' => 'barber@abv.bg',
                'roles4' => '["ROLE_BARBER"]',
                'password4' => $hashedPassword,
                'first_name4' => 'Barber',
                'last_name4' => 'Barber',
                'nick_name4' => 'Barbara',
                'phone4' => '0999999999',
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
