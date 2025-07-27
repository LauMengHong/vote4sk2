-- Create database
CREATE DATABASE IF NOT EXISTS class_voting;
USE class_voting;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    has_voted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Candidates table
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    votes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Votes table
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    candidate_id INT,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);

-- Settings table
CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@4sk2.com', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p02tr9tkzFibkk4w0kJb.a6i', 'admin');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('voting_open', '1'),
('show_results', '0');

-- Insert Class 4SK2 candidates
INSERT INTO candidates (name, description) VALUES 
('Ambert Chan Wye Zhe', 'Class 4SK2 - Talented impersonator with great comedic timing'),
('Arwin Al Tharman', 'Class 4SK2 - Master of voice impressions and character acting'),
('Brendon Foong Wei Le', 'Class 4SK2 - Creative performer with unique style'),
('Chuah Kai Xian', 'Class 4SK2 - Energetic entertainer with natural charisma'),
('Clayton Tai Kar Poh', 'Class 4SK2 - Skilled mimic with attention to detail'),
('Darren Ng Shun Hoong', 'Class 4SK2 - Versatile performer with great stage presence'),
('Heah Aun Sheng', 'Class 4SK2 - Funny and engaging impersonation artist'),
('Ian Beh Zhi Ming', 'Class 4SK2 - Dynamic performer with excellent timing'),
('Ivanjit Singh Jaswal', 'Class 4SK2 - Charismatic entertainer with natural talent'),
('Khaw Eason', 'Class 4SK2 - Creative impersonator with unique approach'),
('Lau Meng Hong', 'Class 4SK2 - Skilled performer with great character work'),
('Lee Jia Jun', 'Class 4SK2 - Talented mimic with excellent observation skills'),
('Leonard Low Zhen Wei', 'Class 4SK2 - Entertaining performer with natural flair'),
('Leow Kai Jie', 'Class 4SK2 - Creative artist with impressive range'),
('Lim Zi Shen', 'Class 4SK2 - Engaging performer with great energy'),
('Lok Yen Tao', 'Class 4SK2 - Skilled impersonator with attention to detail'),
('Long Jia Ji', 'Class 4SK2 - Dynamic entertainer with natural charisma'),
('Low Ze Zayne', 'Class 4SK2 - Talented performer with unique style'),
('Tan Zi Hong', 'Class 4SK2 - Creative impersonator with great timing'),
('Tong Yue Luong', 'Class 4SK2 - Versatile performer with excellent skills'),
('Woon Jun Lin', 'Class 4SK2 - Engaging entertainer with natural talent');