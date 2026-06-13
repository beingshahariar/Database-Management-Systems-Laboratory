-- =======================
-- Create Database
-- =======================
DROP DATABASE IF EXISTS food_db;
CREATE DATABASE food_db;
USE food_db;

-- =======================
-- Admin Table
-- =======================
CREATE TABLE admin (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  name VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  deleted_at TIMESTAMP NULL DEFAULT NULL, 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admin (id, name, password) VALUES
(UUID(), 'admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2');

-- =======================
-- Users Table
-- =======================
CREATE TABLE users (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  number VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  deleted_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_users_email ON users(email);

INSERT INTO users (id, name, email, number, password) VALUES
(UUID(), 'Alice', 'alice@example.com', '01722222222', 'pass123'),
(UUID(), 'Bob', 'bob@example.com', '01733333333', 'pass456');

-- =======================
-- User Addresses
-- =======================
CREATE TABLE user_addresses (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  user_id CHAR(36) NOT NULL,
  address_type ENUM('home','work','billing','shipping') DEFAULT 'home',
  address_text VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_user_addresses_user_id ON user_addresses(user_id);

-- =======================
-- Products Table
-- =======================
CREATE TABLE products (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  name VARCHAR(100) NOT NULL,
  category VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255),
  calories DECIMAL(10,2) DEFAULT 0,
  is_green TINYINT(1) NOT NULL DEFAULT 0,
  deleted_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_price ON products(price);

-- =======================
-- Carbon Impact Table
-- =======================
CREATE TABLE carbon_impact (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  product_id CHAR(36) NOT NULL,
  carbon_value FLOAT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_carbon_product_id ON carbon_impact(product_id);

-- =======================
-- Riders Table 
-- =======================
CREATE TABLE riders (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  current_location VARCHAR(255) DEFAULT NULL,
  deleted_at TIMESTAMP NULL DEFAULT NULL, 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_riders_status ON riders(status);
CREATE INDEX idx_riders_location ON riders(current_location);

-- =======================
-- Orders Table
-- =======================
CREATE TABLE orders (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  user_id CHAR(36) NOT NULL,
  address_id CHAR(36) NULL DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  number VARCHAR(20) NOT NULL,
  email VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  method VARCHAR(50),
  total_products INT,
  total_price DECIMAL(10,2), 
  total_carbon_impact DECIMAL(10, 2) NULL DEFAULT 0.00,
  total_calories INT NULL DEFAULT 0,
  placed_on DATETIME DEFAULT CURRENT_TIMESTAMP,
  payment_status VARCHAR(20) DEFAULT 'pending',
  rider_id CHAR(36) DEFAULT NULL,
  delivery_status ENUM('pending','picked','delivered','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (rider_id) REFERENCES riders(id) ON DELETE SET NULL,
  FOREIGN KEY (address_id) REFERENCES user_addresses(id) ON DELETE SET NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes (Existing indexes remain the same)
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_rider_id ON orders(rider_id);
CREATE INDEX idx_orders_address_id ON orders(address_id); 
CREATE INDEX idx_orders_delivery_status ON orders(delivery_status);

-- =======================
-- Order Items Table
-- =======================
CREATE TABLE order_items (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  order_id CHAR(36) NOT NULL,
  product_id CHAR(36) NOT NULL,
  quantity INT NOT NULL,
  price_at_purchase DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);

-- =======================
-- Cart Table
-- =======================
CREATE TABLE cart (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  user_id CHAR(36) NOT NULL,
  product_id CHAR(36) NOT NULL,
  quantity INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE INDEX idx_cart_user_id ON cart(user_id);
CREATE INDEX idx_cart_product_id ON cart(product_id);

-- =======================
-- Messages Table
-- =======================
CREATE TABLE messages (
  id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
  user_id CHAR(36) NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_messages_user_id ON messages(user_id);

-- =======================
-- Views
-- =======================
-- Total orders per user
CREATE VIEW user_order_summary AS
SELECT u.id AS user_id, u.name, u.email,
       COUNT(o.id) AS total_orders,
       SUM(o.total_price) AS total_spent
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id;

-- Most popular products
CREATE VIEW popular_products AS
SELECT p.id AS product_id, p.name, p.category,
       SUM(oi.quantity) AS total_sold
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id
ORDER BY total_sold DESC;

-- Active riders with current location
CREATE VIEW active_riders AS
SELECT id, name, email, current_location
FROM riders
WHERE status='active';



