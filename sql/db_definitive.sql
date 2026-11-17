CREATE DATABASE IF NOT EXISTS oficina_mecanica;
USE oficina_mecanica;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'mechanic') NOT NULL DEFAULT 'mechanic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    document VARCHAR(40) NULL,
    phone VARCHAR(25) NULL,
    email VARCHAR(120) NULL,
    address VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    brand VARCHAR(80) NOT NULL,
    model VARCHAR(120) NOT NULL,
    plate VARCHAR(20) NOT NULL,
    year VARCHAR(10) NOT NULL,
    color VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicles_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS service_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    mechanic_id INT NOT NULL,
    description TEXT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    scheduled_end DATETIME NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'agendado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_service_orders_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_service_orders_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_service_orders_mechanic FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_service_order_dates CHECK (scheduled_end > scheduled_at)
);

CREATE TABLE IF NOT EXISTS service_order_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NULL,
    user_id INT NOT NULL,
    operation VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_order FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE INDEX idx_orders_mechanic_datetime ON service_orders(mechanic_id, scheduled_at, scheduled_end);
CREATE INDEX idx_orders_client_date ON service_orders(client_id, scheduled_at);


USE oficina_mecanica;

INSERT INTO users (name, username, password, role) VALUES
('Administrador', 'admin', 'admin123', 'admin'),
('Mecânico', 'mecanico', 'mecanico123', 'mechanic')
ON DUPLICATE KEY UPDATE username = VALUES(username);

INSERT INTO clients (name, document, phone, email, address)
VALUES
('João da Silva', '123.456.789-00', '(11) 99999-0001', 'joao@email.com', 'Rua A, 100'),
('Maria Oliveira', '987.654.321-00', '(11) 98888-0002', 'maria@email.com', 'Rua B, 200')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO vehicles (client_id, brand, model, plate, year, color)
VALUES
((SELECT id FROM clients WHERE document = '123.456.789-00'), 'Ford', 'Fiesta', 'ABC-1234', '2020', 'Prata'),
((SELECT id FROM clients WHERE document = '987.654.321-00'), 'Chevrolet', 'Onix', 'XYZ-4321', '2022', 'Vermelho')
ON DUPLICATE KEY UPDATE plate = VALUES(plate);


-- Execute uma única vez em bancos já existentes para preservar o histórico
-- quando uma ordem de serviço for excluída.
ALTER TABLE service_order_history
    DROP FOREIGN KEY fk_history_order;

ALTER TABLE service_order_history
    MODIFY order_id INT NULL;

ALTER TABLE service_order_history
    ADD CONSTRAINT fk_history_order
    FOREIGN KEY (order_id) REFERENCES service_orders(id)
    ON DELETE SET NULL ON UPDATE CASCADE;
