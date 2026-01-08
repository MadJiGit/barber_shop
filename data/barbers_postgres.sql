-- Barber Shop Users/Barbers
-- PostgreSQL version

INSERT INTO "user" (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
VALUES
('super_admin@abv.bg', '["ROLE_SUPER_ADMIN"]'::json, '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Super', 'Adminev', 'Super Admina', '0888888811', NOW(), true, false),
('admin@abv.bg', '["ROLE_ADMIN"]'::json, '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Admin', 'Adminev', 'Admina', '0888888811', NOW(), true, false),
('barber_senior@abv.bg', '["ROLE_BARBER_SENIOR"]'::json, '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Senior', 'Barber', 'Seniora', '0888888888', NOW(), true, false),
('barber@abv.bg', '["ROLE_BARBER"]'::json, '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Barber', 'Barber', 'Barbara', '0999999999', NOW(), true, false),
('client_1@abv.bg', '["ROLE_CLIENT"]'::json, '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Client', 'First', 'Clienta', '0999999966', NOW(), true, false);