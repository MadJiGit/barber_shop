-- Barber Shop Users/Barbers
-- MySQL version

INSERT INTO `user` (email, roles, password, first_name, last_name, nick_name, phone, date_added, is_active, is_banned)
VALUES
('super_admin@abv.bg', '["ROLE_SUPER_ADMIN"]', '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Super', 'Adminev', 'Super Admina', '0888888811', NOW(), 1, 0),
('admin@abv.bg', '["ROLE_ADMIN"]', '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Admin', 'Adminev', 'Admina', '0888888811', NOW(), 1, 0),
('barber_senior@abv.bg', '["ROLE_BARBER_SENIOR"]', '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Senior', 'Barber', 'Seniora', '0888888888', NOW(), 1, 0),
('barber@abv.bg', '["ROLE_BARBER"]', '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Barber', 'Barber', 'Barbara', '0999999999', NOW(), 1, 0),
('client_1@abv.bg', '["ROLE_CLIENT"]', '$2y$13$LVQe5U5gu3IRJoNZps/OruwBC3EieX6o.Mo4Nba0LGkxACe9kipzS', 'Client', 'First', 'Clienta', '0999999966', NOW(), 1, 0);
