-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS factory_management;
USE factory_management;

-- جدول المستخدمين
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول التصنيفات
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المنتجات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    category_id INT,
    cost_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 0,
    min_quantity INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- جدول الفواتير
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    total_amount DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'transfer', 'card', 'later') DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول عناصر الفواتير
CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- جدول المصاريف
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    description VARCHAR(200) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category ENUM('rent', 'electricity', 'water', 'internet', 'maintenance', 'transportation', 'supplies', 'other') NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الموظفين
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    basic_salary DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الرواتب
CREATE TABLE salaries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL,
    bonuses DECIMAL(10,2) DEFAULT 0,
    deductions DECIMAL(10,2) DEFAULT 0,
    net_salary DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- إدخال بيانات تجريبية
INSERT INTO users (name, email, password, role) VALUES 
('مدير النظام', 'admin@factory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('محاسب', 'accountant@factory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager');

INSERT INTO categories (name, description) VALUES 
('منتجات جاهزة', 'المنتجات النهائية الجاهزة للبيع'),
('مواد خام', 'المواد الأساسية للإنتاج'),
('مستلزمات', 'المستلزمات المساعدة للإنتاج');

INSERT INTO products (name, code, description, category_id, cost_price, selling_price, quantity, min_quantity) VALUES 
('منتج أ', 'PROD001', 'المنتج الرئيسي أ', 1, 50.00, 80.00, 100, 10),
('منتج ب', 'PROD002', 'المنتج الرئيسي ب', 1, 30.00, 50.00, 150, 15),
('مادة خام ١', 'MAT001', 'المادة الخام الأساسية', 2, 10.00, 15.00, 500, 50);

INSERT INTO employees (name, position, phone, email, basic_salary) VALUES 
('أحمد محمد', 'مدير إنتاج', '0512345678', 'ahmed@factory.com', 8000.00),
('فاطمة عبدالله', 'محاسب', '0512345679', 'fatima@factory.com', 6000.00);

-- إضافة الجداول المفقودة
CREATE TABLE IF NOT EXISTS losses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    description VARCHAR(200) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('damage', 'defect', 'theft', 'waste', 'other') NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- تحديث بيانات تجريبية إضافية
INSERT INTO expenses (description, amount, category, date, notes) VALUES 
('إيجار المصنع', 5000.00, 'rent', CURDATE(), 'إيجار شهر حالي'),
('فاتورة كهرباء', 800.00, 'electricity', CURDATE(), 'استهلاك شهر حالي'),
('صيانة آلات', 1200.00, 'maintenance', CURDATE(), 'صيانة دورية');

INSERT INTO losses (description, amount, type, date, notes) VALUES 
('تلف منتجات بسبب الرطوبة', 500.00, 'damage', CURDATE(), 'تم التخلص من المنتجات التالفة'),
('عيوب في الإنتاج', 300.00, 'defect', CURDATE(), 'مرتجعات بسبب عيوب');

INSERT INTO salaries (employee_id, basic_salary, bonuses, deductions, net_salary, payment_date, notes) VALUES 
(1, 8000.00, 500.00, 200.00, 8300.00, CURDATE(), 'راتب شهر حالي + مكافأة أداء'),
(2, 6000.00, 300.00, 100.00, 6200.00, CURDATE(), 'راتب شهر حالي');