<?php
require_once __DIR__ . '/config/bootstrap.php'; require_once __DIR__ . '/repositories/ClientRepository.php'; require_once __DIR__ . '/repositories/VehicleRepository.php';
requireAuth(); $user=currentUser();
if($_SERVER['REQUEST_METHOD']==='POST'){
 requireRole(['admin']); $action=$_POST['action']??'create'; $id=(int)($_POST['id']??0); $clientId=(int)($_POST['client_id']??0);
 if($action!=='delete' && ($clientId<=0 || !ClientRepository::findById($clientId))){$_SESSION['flash_message']='Selecione um cliente válido.';$_SESSION['flash_type']='error';}
 else {try{if($action==='delete'){VehicleRepository::delete($id);$msg='Veículo excluído com sucesso.';}else{$data=['client_id'=>$clientId,'brand'=>trim((string)($_POST['brand']??'')),'model'=>trim((string)($_POST['model']??'')),'plate'=>strtoupper(trim((string)($_POST['plate']??''))),'year'=>trim((string)($_POST['year']??'')),'color'=>trim((string)($_POST['color']??''))];if(in_array('',[$data['brand'],$data['model'],$data['plate'],$data['year'],$data['color']],true))throw new RuntimeException();if($action==='update'){VehicleRepository::update($id,$data);$msg='Veículo atualizado com sucesso.';}else{VehicleRepository::create($data);$msg='Veículo cadastrado com sucesso.';}}$_SESSION['flash_message']=$msg;$_SESSION['flash_type']='success';}catch(Throwable $ex){$_SESSION['flash_message']='Não foi possível concluir a operação. Verifique os dados e se o veículo possui ordens vinculadas.';$_SESSION['flash_type']='error';}}
 redirect('veiculos.php');
}
$vehicles=VehicleRepository::listAll();$clients=ClientRepository::listAll();$editing=isset($_GET['edit'])?VehicleRepository::findById((int)$_GET['edit']):null;$message=$_SESSION['flash_message']??'';$type=$_SESSION['flash_type']??'success';unset($_SESSION['flash_message'],$_SESSION['flash_type']);$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Veículos - Oficina Mecânica</title>
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
            <?php echo $editing?'Editar veículo':'Cadastrar veículo'; ?>
        </h3>
        <form method="post" class="grid-form">
            <input type="hidden" name="action" value="<?php echo $editing?'update':'create'; ?>">
            <?php if($editing): ?>
                <input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>">
                <?php endif; ?>
                <div class="field">
                    <label>Cliente</label>
                    <select name="client_id" required>
                        <option value="">Selecione</option>
                        <?php foreach($clients as $client): ?>
                            <option value="<?php echo (int)$client['id']; ?>" <?php echo $editing&&(int)$editing['client_id']===(int)$client['id']?'selected':''; ?>>
                                <?php echo $e($client['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Marca</label>
                        <input name="brand" required value="<?php echo $e($editing['brand']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Modelo</label>
                        <input name="model" required value="<?php echo $e($editing['model']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Placa</label>
                        <input name="plate" required value="<?php echo $e($editing['plate']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Ano</label>
                        <input name="year" required value="<?php echo $e($editing['year']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Cor</label>
                        <input name="color" required value="<?php echo $e($editing['color']??''); ?>">
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary">
                            <?php echo $editing?'Salvar alterações':'Salvar'; ?>
                        </button>
                        <?php if($editing): ?>
                            <a class="btn btn-secondary" href="veiculos.php">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>
                <?php endif; ?>
<section class="card">
    <h3>Veículos cadastrados</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Placa</th>
                    <th>Ano</th>
                    <?php if($user['role']==='admin'): ?>
                        <th>Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!$vehicles): ?>
                        <tr>
                            <td colspan="6">Nenhum veículo cadastrado.</td>
                        </tr>
                        <?php else: foreach($vehicles as $vehicle): $owner=['name'=>$vehicle['client_name']??'Cliente']; ?>
                        <tr>
                            <td>
                                <?php echo $e($owner['name']??'Cliente'); ?>
                            </td>
                            <td>
                                <?php echo $e($vehicle['brand']); ?>
                            </td>
                            <td>
                                <?php echo $e($vehicle['model']); ?>
                            </td>
                            <td>
                                <?php echo $e($vehicle['plate']); ?>
                            </td>
                            <td>
                                <?php echo $e($vehicle['year']); ?>
                            </td>
                            <?php if($user['role']==='admin'): ?>
                                <td class="action-buttons">
                                    <a class="btn btn-small btn-primary" href="?edit=<?php echo (int)$vehicle['id']; ?>">Editar</a>
                                    <form method="post" onsubmit="return confirm('Excluir este veículo?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$vehicle['id']; ?>">
                                        <button class="btn btn-small btn-danger">Excluir</button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
</main>
</body>
</html>
