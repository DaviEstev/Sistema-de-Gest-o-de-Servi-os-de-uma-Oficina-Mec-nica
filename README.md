# Sistema de Gestão de Serviços de Oficina Mecânica

Projeto PHP com HTML, CSS, MySQL e sessões para controle de autenticação e permissões.

## Estrutura principal
- config/
- controllers/
- models/
- repositories/
- services/
- views/
- assets/
- sql/
- tests/

## Configuração
1. Crie o banco de dados MySQL seguindo o script em sql/schema.sql.
2. Execute o seed em sql/seed.sql.
3. Ajuste as credenciais do banco em config/database.php ou variáveis de ambiente.
4. Acesse index.php para fazer login.

## Usuários iniciais
- admin / admin123
- mecanico / mecanico123

## Regras de negócio implementadas
- Autenticação por sessão.
- Perfis admin e mechanic.
- Proteção de acesso por backend.
- Verificação de sobreposição de horários do mecânico.
- Registro de histórico de operações.
- Paginação e filtros em ordens de serviço.
