CREATE DATABASE IF NOT EXISTS maikot_business CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE maikot_business;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_code VARCHAR(30) NOT NULL UNIQUE,
  product_name VARCHAR(150) NOT NULL,
  category VARCHAR(100) DEFAULT NULL,
  brand_model VARCHAR(120) DEFAULT NULL,
  unit VARCHAR(30) NOT NULL DEFAULT 'Piece',
  selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_code VARCHAR(30) NOT NULL UNIQUE,
  supplier_name VARCHAR(150) NOT NULL,
  contact_person VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  address VARCHAR(180) DEFAULT NULL,
  pan_vat VARCHAR(50) DEFAULT NULL,
  remarks TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_date DATE NOT NULL,
  product_id INT NOT NULL,
  supplier_id INT NOT NULL,
  qty DECIMAL(12,2) NOT NULL,
  purchase_rate DECIMAL(12,2) NOT NULL,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  transport DECIMAL(12,2) NOT NULL DEFAULT 0,
  other_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_purchase_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT fk_purchase_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

INSERT INTO products (product_code, product_name, category, brand_model, unit, selling_price) VALUES
('P001','A4 Paper 70 GSM','Stationery','Generic','Ream',550),
('P002','4 in 1 Notebook','Stationery','Kids Practice','Piece',180),
('P003','3 in 1 Pen','Stationery','Multi-use','Piece',120)
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name);

INSERT INTO suppliers (supplier_code, supplier_name) VALUES
('S001','Wholesale Supplier A'),
('S002','Wholesale Supplier B'),
('S003','Wholesale Supplier C')
ON DUPLICATE KEY UPDATE supplier_name=VALUES(supplier_name);