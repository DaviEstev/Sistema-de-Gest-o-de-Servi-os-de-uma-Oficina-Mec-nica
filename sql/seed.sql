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
