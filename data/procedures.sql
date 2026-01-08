-- Barber Shop Procedures/Services
-- Insert basic procedures with pricing for master and junior barbers

INSERT INTO `procedures` (`type`, `price_master`, `price_junior`, `duration_master`, `duration_junior`, `available`, `date_added`) VALUES
-- Haircuts
('Haircut - Men', '25.00', '20.00', 30, 40, 1, NOW()),
('Haircut - Kids', '15.00', '12.00', 20, 25, 1, NOW()),
('Haircut - Senior', '20.00', '18.00', 30, 40, 1, NOW()),

-- Beard Services
('Beard Trim', '15.00', '12.00', 20, 25, 1, NOW()),
('Beard Shaping', '18.00', '15.00', 25, 30, 1, NOW()),
('Hot Towel Shave', '30.00', '25.00', 40, 50, 1, NOW()),

-- Combo Services
('Haircut + Beard Trim', '35.00', '28.00', 45, 60, 1, NOW()),
('Haircut + Beard Shaping', '38.00', '32.00', 50, 65, 1, NOW()),
('Haircut + Hot Towel Shave', '50.00', '42.00', 60, 75, 1, NOW()),

-- Premium Services
('Premium Haircut + Styling', '40.00', '35.00', 45, 55, 0, NOW()),
('Head Massage', '20.00', '15.00', 20, 25, 0, NOW()),
('Hair Coloring', '50.00', '45.00', 90, 120, 0, NOW()),
('Highlights', '60.00', '55.00', 120, 150, 0, NOW()),

-- Special Services
('Wedding/Event Styling', '80.00', '70.00', 90, 120, 0, NOW()),
('Consultation', '0.00', '0.00', 15, 15, 0, NOW());
