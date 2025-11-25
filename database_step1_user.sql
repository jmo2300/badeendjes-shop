-- Maak de database aan 
-- Deze gebruikers als Root aanmaken
-- sudo mysql -u root -p < database_step1_user.sql
-- ===============================================================
CREATE DATABASE IF NOT EXISTS badeendjes_shop CHARACTER SET utf8mb4 COLLATE
utf8mb4_unicode_ci; USE badeendjes_shop;

CREATE USER 'badeendadmin'@'localhost' IDENTIFIED BY 'badeendpw'; GRANT ALL PRIVILEGES ON
badeendjes_shop.* TO 'badeendadmin'@'localhost';
