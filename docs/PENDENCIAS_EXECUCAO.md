# Itens que dependem da execução no ambiente local

As alterações de código e os documentos-base foram preparados. Ainda é necessário executar a aplicação conectada ao banco MySQL para comprovar os cenários e capturar as evidências reais.

1. Execute `sql/schema.sql` em uma instalação nova do banco; para um banco já existente, aplique uma única vez `docs/migrations/2026-11-16-historico-exclusao.sql`.
2. Inicie Apache e MySQL e configure as variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` se os valores padrão do projeto não forem adequados.
3. Execute os três cenários descritos em `docs/plano_testes.docx`.
4. Salve as capturas reais como `docs/testes/cenario1.png`, `cenario2.png` e `cenario3.png`.
5. Preencha os resultados obtidos, conclusões e ambiente de execução em `resultados.docx`.

A implementação de cadastro de usuários com tratamento seguro de senhas e os demais ajustes de autenticação/segurança foram deixados intencionalmente para a etapa que o desenvolvedor informou que fará depois. Versionamento, branch, commits e Pull Request também não foram alterados.
