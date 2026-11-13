<?php

require_once __DIR__ . '/config/database.php';

$pdoMysqlAvailable = extension_loaded('pdo_mysql');
$databaseName = null;
$mysqlVersion = null;
$connectionError = null;

if ($pdoMysqlAvailable) {
    try {
        $connection = getDatabaseConnection();
        $result = $connection->query('SELECT DATABASE() AS database_name, VERSION() AS mysql_version');
        $details = $result->fetch();
        $databaseName = $details['database_name'] ?? null;
        $mysqlVersion = $details['mysql_version'] ?? null;
    } catch (Throwable $exception) {
        $connectionError = $exception->getMessage();
    }
}

$connectionSucceeded = $pdoMysqlAvailable && $connectionError === null;
$escape = static fn (?string $value): string => htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste de conexão PHP e MySQL</title>
    <style>
        body { max-width: 720px; margin: 48px auto; padding: 0 20px; font: 16px/1.5 Arial, sans-serif; color: #202a35; }
        h1 { font-size: 1.6rem; }
        .status { padding: 14px 16px; border-left: 4px solid; background: #f3f5f7; }
        .success { border-color: #16834a; }
        .failure { border-color: #c33b36; }
        dt { margin-top: 14px; font-weight: 700; }
        dd { margin-left: 0; overflow-wrap: anywhere; }
        code { background: #f3f5f7; padding: 2px 5px; }
    </style>
</head>
<body>
    <h1>Teste de conexão PHP e MySQL</h1>

    <p class="status <?= $connectionSucceeded ? 'success' : 'failure' ?>">
        <?= $connectionSucceeded
            ? 'Conexão realizada: o PHP conectou ao MySQL e executou uma consulta.'
            : 'Não foi possível confirmar a conexão com o MySQL.' ?>
    </p>

    <dl>
        <dt>PHP</dt>
        <dd>Executando (versão <?= $escape(PHP_VERSION) ?>)</dd>

        <dt>Driver PDO MySQL</dt>
        <dd><?= $pdoMysqlAvailable ? 'Disponível' : 'Não disponível; habilite pdo_mysql no PHP.' ?></dd>

        <?php if ($connectionSucceeded): ?>
            <dt>Banco conectado</dt>
            <dd><?= $escape($databaseName) ?></dd>

            <dt>Versão do MySQL</dt>
            <dd><?= $escape($mysqlVersion) ?></dd>
        <?php elseif ($connectionError !== null): ?>
            <dt>Erro retornado</dt>
            <dd><?= $escape($connectionError) ?></dd>
        <?php endif; ?>
    </dl>

    <p>Inicie Apache e MySQL no XAMPP e abra <code>http://localhost/Sistema%20Noemi/teste_conexao.php</code>.</p>
    <p>Após o teste, remova este arquivo para não deixar a página de diagnóstico publicada.</p>
</body>
</html>