USE oficina_mecanica;

INSERT INTO users (name, username, password_hash, role) VALUES
('Administrador', 'admin', '$2y$10$72QhktjEv3Ar1vX2JYTX0u2q4dA8vfB8M9R7f5R3A7oJt4HhV4F0K', 'admin'),
('Mecânico', 'mecanico', '$2y$10$08f9kLoQioOUItM/5KQz0.5xkQhDMgKt89b2w8R3ZrwsC8wUjD5mW', 'mechanic')
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
