<?php


$db_config = [
    'host' => 'sql100.infinityfree.com',
    'username' => 'if0_40600774',
    'password' => 'Gxsm0iUq0kbA',
    'database' => 'if0_40600774_pos_system'
];


$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password']);

$sql = "CREATE DATABASE IF NOT EXISTS " . $db_config['database'];
$conn->query($sql);
$conn->select_db($db_config['database']);


$queries = [

"
CREATE TABLE IF NOT EXISTS branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    branch_code CHAR(3) UNIQUE NOT NULL,
    location VARCHAR(200),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
",


"
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    branch_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB;
",


"
CREATE TABLE IF NOT EXISTS license (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_code VARCHAR(100) UNIQUE NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    activation_date DATETIME NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
",


"
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS stock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    branch_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    alert_level INT DEFAULT 5,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_branch (product_id, branch_id)
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_number VARCHAR(50) UNIQUE NOT NULL,
    branch_id INT NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card') NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS repairs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    repair_number VARCHAR(50) UNIQUE NOT NULL,
    branch_id INT NOT NULL,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    device_name VARCHAR(100) NOT NULL,
    imei_last5 VARCHAR(5),
    error_description TEXT NOT NULL,
    repair_cost DECIMAL(10,2) NOT NULL,
    parts_replaced TEXT,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_date DATETIME NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS returns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_number VARCHAR(50) UNIQUE NOT NULL,
    branch_id INT NOT NULL,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    customer_name VARCHAR(100),
    quantity INT NOT NULL,
    reason TEXT,
    replacement_given TINYINT(1) DEFAULT 0,
    replacement_product_id INT NULL,
    replacement_quantity INT NULL,
    not_working TINYINT(1) DEFAULT 0,
    return_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (replacement_product_id) REFERENCES products(id)
) ENGINE=InnoDB;
",

"
CREATE TABLE IF NOT EXISTS stock_transfers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    from_branch_id INT NOT NULL,
    to_branch_id INT NOT NULL,
    quantity INT NOT NULL,
    transferred_by INT NOT NULL,
    transfer_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (from_branch_id) REFERENCES branches(id),
    FOREIGN KEY (to_branch_id) REFERENCES branches(id),
    FOREIGN KEY (transferred_by) REFERENCES users(id)
) ENGINE=InnoDB;
"
];


foreach ($queries as $q) {
    if (trim($q) != "") {
        if (!$conn->query($q)) {
            echo "Error: " . $conn->error . "<br>";
        }
    }
}


$admin_password = password_hash('admin123', PASSWORD_BCRYPT);
$conn->query("INSERT INTO users (username, password, full_name, role) 
              VALUES ('admin', '$admin_password', 'System Administrator', 'admin')");

echo "Database installed successfully!";
?>
