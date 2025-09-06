
<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
    
    public function createTables() {
        $sql = "
        -- Таблица категорий услуг
        CREATE TABLE IF NOT EXISTS service_categories (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name_sk VARCHAR(255) NOT NULL,
            name_ru VARCHAR(255) NOT NULL,
            name_ua VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description_sk TEXT,
            description_ru TEXT,
            description_ua TEXT,
            order_num INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Таблица услуг
        CREATE TABLE IF NOT EXISTS services (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            category_id VARCHAR(36) NOT NULL,
            name_sk VARCHAR(255) NOT NULL,
            name_ru VARCHAR(255) NOT NULL,
            name_ua VARCHAR(255) NOT NULL,
            description_sk TEXT,
            description_ru TEXT,
            description_ua TEXT,
            price DECIMAL(10,2) NOT NULL,
            duration INT NOT NULL,
            whatsapp_number VARCHAR(20) DEFAULT '+421',
            is_active BOOLEAN DEFAULT TRUE,
            order_num INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE CASCADE
        );

        -- Таблица временных слотов
        CREATE TABLE IF NOT EXISTS time_slots (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            date DATE NOT NULL,
            time TIME NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_datetime (date, time)
        );

        -- Таблица бронирований
        CREATE TABLE IF NOT EXISTS bookings (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            service_id VARCHAR(36) NOT NULL,
            time_slot_id VARCHAR(36) NOT NULL,
            client_name VARCHAR(255),
            client_phone VARCHAR(20) NOT NULL,
            message TEXT,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES services(id),
            FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
        );

        -- Таблица отзывов
        CREATE TABLE IF NOT EXISTS reviews (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            client_name VARCHAR(255) NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            text_sk TEXT NOT NULL,
            text_ru TEXT NOT NULL,
            text_ua TEXT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            order_num INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Таблица статей блога
        CREATE TABLE IF NOT EXISTS blog_posts (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            title_sk VARCHAR(255) NOT NULL,
            title_ru VARCHAR(255) NOT NULL,
            title_ua VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            excerpt_sk TEXT,
            excerpt_ru TEXT,
            excerpt_ua TEXT,
            content_sk LONGTEXT NOT NULL,
            content_ru LONGTEXT NOT NULL,
            content_ua LONGTEXT NOT NULL,
            featured_image VARCHAR(255),
            images JSON,
            is_published BOOLEAN DEFAULT FALSE,
            published_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Таблица галереи
        CREATE TABLE IF NOT EXISTS gallery_images (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            filename VARCHAR(255) NOT NULL,
            alt_sk VARCHAR(255),
            alt_ru VARCHAR(255),
            alt_ua VARCHAR(255),
            description_sk TEXT,
            description_ru TEXT,
            description_ua TEXT,
            order_num INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Таблица контактов
        CREATE TABLE IF NOT EXISTS contacts (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(255),
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Таблица пользователей админки
        CREATE TABLE IF NOT EXISTS admin_users (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            role ENUM('admin', 'editor') DEFAULT 'editor',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";
        
        $conn = $this->getConnection();
        $conn->exec($sql);
    }
}
?>
