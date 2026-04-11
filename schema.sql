-- Drop existing smiths_grips database and create a new one
DROP DATABASE IF EXISTS smiths_grips;
CREATE DATABASE smiths_grips;
USE smiths_grips;

-- Define database tables
CREATE TABLE grip_prices (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    brand VARCHAR(50) NOT NULL,
    club_type VARCHAR(20) NOT NULL,
    min_price DECIMAL(5,2) NOT NULL,
    max_price DECIMAL(5,2) NOT NULL
);

INSERT INTO grip_prices (brand, club_type, min_price, max_price) VALUES
('Golf Pride', 'swinging', 10.99, 15.99),
('Golf Pride', 'putter', 14.99, 34.99),
('Winn', 'swinging', 5.99, 13.39),
('Winn', 'putter', 13.39, 26.99),
('Super Stroke', 'swinging', 5.99, 9.99),
('Super Stroke', 'putter', 34.99, 39.99);