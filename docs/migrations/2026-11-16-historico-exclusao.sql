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
