-- Sinath Travels Database Schema
-- MySQL 5.7+ or 8.0+

CREATE DATABASE IF NOT EXISTS sinath_travels 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sinath_travels;

-- Packages Table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('tour', 'visa', 'ticket', 'offer') NOT NULL DEFAULT 'tour',
    title_en VARCHAR(255) NOT NULL,
    title_si VARCHAR(255),
    title_ta VARCHAR(255),
    description_en TEXT NOT NULL,
    description_si TEXT,
    description_ta TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration VARCHAR(50),
    image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inquiries Table
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promotions/Banners Table
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(500),
    display_order INT DEFAULT 0,
    active_status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active_status),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Table (for the main services display)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(50) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_si VARCHAR(255),
    title_ta VARCHAR(255),
    description_en TEXT NOT NULL,
    description_si TEXT,
    description_ta TEXT,
    display_order INT DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Data
INSERT INTO packages (category, title_en, title_si, title_ta, description_en, description_si, description_ta, price, duration, image) VALUES
('tour', 'Sri Lanka Heritage Tour', 'ශ්‍රී ලංකා උරුම සංචාරය', 'இலங்கை பாரம்பரிய சுற்றுலா', 'Explore the ancient wonders of Sigiriya, Polonnaruwa, and Kandy.', 'සීගිරිය, පොළොන්නරුව සහ මහනුවර ඓතිහාසික ස්ථාන නරඹන්න.', 'சிகிரியா, பொலன்னறுவை மற்றும் கண்டி ஆகியவற்றின் பழமையான அதிசயங்களை ஆராயுங்கள்.', 500.00, '5 Days', 'sri_lanka_sigiriya_r_ddcb0491.jpg'),
('tour', 'Dubai Shopping Festival', 'ඩුබායි සාප්පු උත්සවය', 'துபாய் ஷாப்பிங் திருவிழா', 'Experience the luxury and shopping extravaganza of Dubai.', 'ඩුබායි සුඛෝපභෝගී සාප්පු අත්දැකීම.', 'துபாயின் ஆடம்பரம் மற்றும் ஷாப்பிங் விழாவை அனுபவியுங்கள்.', 800.00, '4 Days', 'dubai_skyline_with_b_8fae68a6.jpg'),
('tour', 'Maldives Luxury Escape', 'මාලදිවයින සුඛෝපභෝගී නිවාඩුව', 'மாலத்தீவு ஆடம்பர விடுமுறை', 'Relax in overwater villas with crystal clear waters.', 'ජලය මත පිහිටි විලා වල විවේක ගන්න.', 'தெளிவான நீருடன் கூடிய மேல்நீர் வில்லாக்களில் ஓய்வெடுங்கள்.', 1200.00, '3 Days', 'maldives_water_villa_e130bb66.jpg');

INSERT INTO services (icon, title_en, title_si, title_ta, description_en, description_si, description_ta, display_order) VALUES
('Plane', 'Air Ticketing', 'ගුවන් ටිකට්පත්', 'விமான டிக்கெட்', 'Best rates for all international and domestic flights. We handle all airline reservations.', 'සියලුම ගුවන් ටිකට්පත් සඳහා හොඳම මිල ගණන්.', 'அனைத்து சர்வதேச மற்றும் உள்நாட்டு விமானங்களுக்கும் சிறந்த விலைகள்.', 1),
('FileCheck', 'Visa Services', 'වීසා සේවා', 'விசா சேவைகள்', 'Hassle-free visa assistance for Dubai, Singapore, Malaysia, and more.', 'ඩුබායි, සිංගප්පූරුව, මැලේසියාව සඳහා වීසා සහාය.', 'துபாய், சிங்கப்பூர், மலேசியா மற்றும் பலவற்றிற்கான எளிதான விசா உதவி.', 2),
('Map', 'Tour Packages', 'සංචාරක පැකේජ', 'சுற்றுலா தொகுப்புகள்', 'Customized holiday packages for families, couples, and groups.', 'පවුල්, ජෝඩු සහ කණ්ඩායම් සඳහා සංචාරක පැකේජ.', 'குடும்பங்கள், ஜோடிகள் மற்றும் குழுக்களுக்கான தனிப்பயனாக்கப்பட்ட விடுமுறை தொகுப்புகள்.', 3);

INSERT INTO promotions (title, image, active_status, display_order) VALUES
('Summer Special Offer', 'summer-promo.jpg', 1, 1),
('Early Bird Discounts', 'earlybird-promo.jpg', 1, 2);