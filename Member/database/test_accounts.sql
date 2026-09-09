-- =====================================================================
-- database/test_accounts.sql
-- ---------------------------------------------------------------------
-- Two ready-made accounts so you can log in straight away without
-- registering first. Run this in phpMyAdmin:
--     phpMyAdmin  ->  car_rental_db  ->  SQL tab  ->  paste  ->  Go
--
-- Both accounts use the password:   password123
--
-- The long $2y$... string is what password_hash() produced for that
-- password. It is one-way: you cannot read the password back out of it.
-- That is exactly the point - even somebody who steals this table
-- cannot log in as these users.
--
-- (You can of course just use View/register.php instead. This file is
--  only here so a demo can start in one click.)
-- =====================================================================

INSERT INTO `users` (`NAME`, `email`, `password_hash`, `role`, `address`, `phone`)
VALUES
('Test Member', 'member@test.com',
 '$2y$12$FX/Wo5WRxD7KmcxMJ1S78eWB6OjFF6XPh1kVbp5Qb7D9xbELA4GlG',
 'member', 'Dhaka', '01700000001'),

('Test Admin', 'admin@test.com',
 '$2y$12$FX/Wo5WRxD7KmcxMJ1S78eWB6OjFF6XPh1kVbp5Qb7D9xbELA4GlG',
 'admin', 'Dhaka', '01700000002');
