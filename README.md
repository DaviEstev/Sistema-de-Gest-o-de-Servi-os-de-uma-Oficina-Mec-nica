# Sistema de Gestão de Serviços de Oficina Mecânica

Aplicação simples feita com PHP, HTML, CSS e MySQL.

## Estrutura principal
- config/
- repositories/
- services/
- assets/
- sql/
- index.php, dashboard.php, clientes.php, veiculos.php e ordens_servico.php

## Configuração
1. Crie o banco de dados MySQL seguindo o script em sql/schema.sql.
2. Execute o seed em sql/seed.sql.
3. Configure as credenciais do banco em config/database.php por meio das variáveis de ambiente DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS.
4. Acesse index.php para fazer login.

## Usuários iniciais
- admin / admin123
- mecanico / mecanico123

As senhas são armazenadas em texto simples, sem hash. Não use esta configuração em um sistema publicado ou com dados reais.

### Banco criado anteriormente
Para adaptar um banco que ainda possui a coluna `password_hash`, execute uma vez:

```sql
ALTER TABLE users CHANGE password_hash password VARCHAR(255) NOT NULL;
UPDATE users SET password = 'admin123' WHERE username = 'admin';
UPDATE users SET password = 'mecanico123' WHERE username = 'mecanico';
```

Altere esses valores para as senhas desejadas antes de usar o sistema.

## Regras de negócio implementadas
- Autenticação por sessão.
- Perfis admin e mechanic.
- Proteção de acesso por backend.
- Verificação de sobreposição de horários do mecânico.
- Registro de histórico de operações.
- Paginação e filtros em ordens de serviço.
