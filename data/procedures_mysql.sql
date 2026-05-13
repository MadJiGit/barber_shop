-- Barber Shop Procedures/Services
-- MySQL version

INSERT INTO `procedures` (type, price_master, price_junior, duration_master, duration_junior, available, date_added) VALUES
-- Haircuts (prices in EUR)
('Haircut - Men', 13.00, 10.00, 30, 40, 1, NOW()),
('Haircut - Kids', 8.00, 6.00, 20, 25, 1, NOW()),
('Haircut - Senior', 10.00, 9.00, 30, 40, 1, NOW()),

-- Beard Services (prices in EUR)
('Beard Trim', 8.00, 6.00, 20, 25, 1, NOW()),
('Beard Shaping', 9.00, 8.00, 25, 30, 1, NOW()),
('Hot Towel Shave', 15.00, 13.00, 40, 50, 1, NOW()),

-- Combo Services (prices in EUR)
('Haircut + Beard Trim', 18.00, 14.00, 45, 60, 1, NOW()),
('Haircut + Beard Shaping', 19.00, 16.00, 50, 65, 1, NOW()),
('Haircut + Hot Towel Shave', 26.00, 21.00, 60, 75, 1, NOW()),

-- Premium Services (prices in EUR)
('Premium Haircut + Styling', 20.00, 18.00, 45, 55, 0, NOW()),
('Head Massage', 10.00, 8.00, 20, 25, 0, NOW()),
('Hair Coloring', 26.00, 23.00, 90, 120, 0, NOW()),
('Highlights', 31.00, 28.00, 120, 150, 0, NOW()),

-- Special Services (prices in EUR)
('Wedding/Event Styling', 40.00, 36.00, 90, 120, 0, NOW()),
('Consultation', 0.00, 0.00, 15, 15, 0, NOW());
