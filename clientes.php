<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/ClientRepository.php';
requireAuth();
$user = currentUser();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRole(['admin']);
    $action = $_POST['action'] ?? 'create';
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    if ($action !== 'delete' && $name === '') {
        $_SESSION['flash_message'] = 'Informe o nome do cliente.'; $_SESSION['flash_type'] = 'error';
    } else {
        try {
            if ($action === 'delete') { ClientRepository::delete($id); $message = 'Cliente excluído com sucesso.'; }
            else {
                $data = ['name'=>$name, 'document'=>trim((string)($_POST['document']??'')), 'phone'=>trim((string)($_POST['phone']??'')), 'email'=>trim((string)($_POST['email']??'')), 'address'=>trim((string)($_POST['address']??''))];
                if ($action === 'update') { ClientRepository::update($id, $data); $message = 'Cliente atualizado com sucesso.'; }
                else { ClientRepository::create($data); $message = 'Cliente cadastrado com sucesso.'; }
            }
            $_SESSION['flash_message'] = $message; $_SESSION['flash_type'] = 'success';
        } catch (Throwable $e) { $_SESSION['flash_message'] = 'Não foi possível concluir a operação. Verifique se o cliente possui veículos ou ordens vinculadas.'; $_SESSION['flash_type'] = 'error'; }
    }
    redirect('clientes.php');
}
$clients = ClientRepository::listAll();
$editing = isset($_GET['edit']) ? ClientRepository::findById((int)$_GET['edit']) : null;
$message = $_SESSION['flash_message'] ?? ''; $type = $_SESSION['flash_type'] ?? 'success'; unset($_SESSION['flash_message'], $_SESSION['flash_type']);
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html><html lang="pt-BR">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Clientes - Oficina Mecânica</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
<header class="topbar">
    <div class="brand">Oficina Mecânica</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="clientes.php">Clientes</a>
        <a href="veiculos.php">Veículos</a>
        <a href="ordens_servico.php">Ordens de Serviço</a>
        <?php if($user['role']==='admin'): ?>
            <a href="usuarios.php">Usuários</a>
            <?php endif; ?>
            <a href="logout.php">Sair</a>
    </nav>
</header>
<main class="container">
<?php if($message!==''): ?>
    <div class="alert alert-<?php echo $type==='error'?'error':'success'; ?>"><?php echo $e($message); ?>
    </div>
<?php endif; ?>
<?php if($user['role']==='admin'): ?>
    <section class="card">
        <h3>
            <?php echo $editing?'Editar cliente':'Cadastrar cliente'; ?>
        </h3>
        <form method="post" class="grid-form">
            <input type="hidden" name="action" value="<?php echo $editing?'update':'create'; ?>">
            <?php if($editing): ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>">
                <?php endif; ?>
<div class="field">
    <label>Nome</label><input name="name" required value="<?php echo $e($editing['name']??''); ?>">
</div>
<div class="field"><label>Documento</label><input name="document" value="<?php echo $e($editing['document']??''); ?>">
</div>
<div class="field">
    <label>Telefone</label>
    <input name="phone" value="<?php echo $e($editing['phone']??''); ?>">
</div>
<div class="field">
    <label>Email</label>
    <input type="email" name="email" value="<?php echo $e($editing['email']??''); ?>">
</div>
<div class="field full">
    <label>Endereço</label>
    <input name="address" value="<?php echo $e($editing['address']??''); ?>">
</div><div class="actions">
    <button class="btn btn-primary" type="submit">
        <?php echo $editing?'Salvar alterações':'Salvar'; ?>
    </button>
    <?php if($editing): ?>
        <a class="btn btn-secondary" href="clientes.php">Cancelar</a>
        <?php endif; ?>
    </div>
</form>
</section>
<?php endif; ?>
<section class="card">
    <h3>Clientes cadastrados</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Documento</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <?php if($user['role']==='admin'): ?>
                        <th>Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
<?php if(!$clients): ?>
    <tr>
        <td colspan="5">Nenhum cliente cadastrado.</td>
    </tr>
    <?php else: foreach($clients as $client): ?>
        <tr>
            <td>
                <?php echo $e($client['name']); ?>
            </td>
            <td>
                <?php echo $e($client['document']); ?>
            </td>
            <td>
                <?php echo $e($client['phone']); ?>
            </td>
            <td>
                <?php echo $e($client['email']); ?>
            </td>
            <?php if($user['role']==='admin'): ?>
                <td class="action-buttons">
                    <a class="btn btn-small btn-primary" href="?edit=<?php echo (int)$client['id']; ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('Excluir este cliente?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$client['id']; ?>">
                        <button class="btn btn-small btn-danger">Excluir</button>
                    </form>
                </td>
                <?php endif; ?></tr><?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
</main>
</body>
</html>
