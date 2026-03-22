CREATE DATABASE IF NOT EXISTS shinan_news_db;
USE shinan_news_db;

-- Table for user registrations
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for the top 5 daily tech news
CREATE TABLE IF NOT EXISTS daily_news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    article_url VARCHAR(255),
    fetch_date DATE NOT NULL,
    UNIQUE KEY unique_date_title (fetch_date, title(100))
);
