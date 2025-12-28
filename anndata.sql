-- ================================
-- CREATE DATABASE
-- ================================
CREATE DATABASE IF NOT EXISTS anndata;
USE anndata;

-- ================================
-- USERS TABLE
-- ================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('farmer','worker') DEFAULT 'farmer',
  mobile VARCHAR(15) NOT NULL,
  address VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','blocked') DEFAULT 'pending',
  crop_limit INT DEFAULT 4,
  tool_limit INT DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- CROPS TABLE (Updated)
-- ================================
CREATE TABLE IF NOT EXISTS crops (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  crop_name VARCHAR(100) NOT NULL,
  area_value DECIMAL(10,2) NOT NULL,
  area_unit ENUM('Bigha','Acre','Hectare') NOT NULL,
  season ENUM('Monsoon','Winter','Summer','All Season') NOT NULL,
  expected_yield VARCHAR(100),
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  approved_by INT NULL,
  area_size VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- TOOLS TABLE (Updated)
-- ================================
CREATE TABLE IF NOT EXISTS tools (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tool_name VARCHAR(100) NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  approved_by INT NULL,
  quantity INT NOT NULL DEFAULT 0,
  rent DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- ADMINS TABLE
-- ================================
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('superadmin','admin') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================
-- ADMIN LOGS TABLE
-- ================================
CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  action VARCHAR(50) NOT NULL,
  table_name VARCHAR(50) NOT NULL,
  target_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS master_tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tool_name VARCHAR(100) UNIQUE NOT NULL,
    rent DECIMAL(10,2) DEFAULT 0,
    available_quantity INT NOT NULL DEFAULT 0
);


-- ================================
-- INSERT DEFAULT SUPERADMIN
-- ================================
INSERT INTO admins (username, email, password, role)
VALUES ('Jaydipsinh', 'jay1@gmail.com', '$2y$10$XxRsiepr.lj.Y.FbzAyvduW3NiUY09gsYYki0G2Rntk8SmbP//B3e', 'superadmin');
