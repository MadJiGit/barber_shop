-- Barber Shop Procedures/Services
-- PostgreSQL version
-- Insert basic procedures with pricing for master and junior barbers



INSERT INTO procedures (type, price_master, price_junior, duration_master, duration_junior, available, date_added) VALUES
-- Haircuts (prices in EUR)
('Haircut - Men', 13.00, 10.00, 30, 40, true, NOW()),
('Haircut - Kids', 8.00, 6.00, 20, 25, true, NOW()),
('Haircut - Senior', 10.00, 9.00, 30, 40, true, NOW()),

-- Beard Services (prices in EUR)
('Beard Trim', 8.00, 6.00, 20, 25, true, NOW()),
('Beard Shaping', 9.00, 8.00, 25, 30, true, NOW()),
('Hot Towel Shave', 15.00, 13.00, 40, 50, true, NOW()),

-- Combo Services (prices in EUR)
('Haircut + Beard Trim', 18.00, 14.00, 45, 60, true, NOW()),
('Haircut + Beard Shaping', 19.00, 16.00, 50, 65, true, NOW()),
('Haircut + Hot Towel Shave', 26.00, 21.00, 60, 75, true, NOW()),

-- Premium Services (prices in EUR)
('Premium Haircut + Styling', 20.00, 18.00, 45, 55, false, NOW()),
('Head Massage', 10.00, 8.00, 20, 25, false, NOW()),
('Hair Coloring', 26.00, 23.00, 90, 120, false, NOW()),
('Highlights', 31.00, 28.00, 120, 150, false, NOW()),

-- Special Services (prices in EUR)
('Wedding/Event Styling', 40.00, 36.00, 90, 120, false, NOW()),
('Consultation', 0.00, 0.00, 15, 15, false, NOW());
