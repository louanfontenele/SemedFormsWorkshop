<?php
session_start();
require 'db.php';

// Função simples para carregar as variáveis do .env
function loadEnv($file) {
    $vars = [];
    if(file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($lines as $line) {
            if(strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $vars[trim($name)] = trim($value);
        }
    }
    return $vars;
}
$env = loadEnv('.env');

// Se não estiver logado, processa o login
if(!isset($_SESSION['admin_logged_in'])) {
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if($username === $env['ADMIN_USER'] && $password === $env['ADMIN_PASS']) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php");
            exit;
        } else {
            $error = "Credenciais inválidas!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
      <meta charset="UTF-8">
      <title>Admin - Login</title>
      <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .login-container { max-width: 400px; margin: 100px auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px; width: 100%; background: #007bff; color: #fff; border: none; }
        button:hover { background: #0056b3; }
        .error { color: red; }
      </style>
    </head>
    <body>
      <div class="login-container">
        <h2>Admin Login</h2>
        <?php if(isset($error)) { echo "<p class='error'>{$error}</p>"; } ?>
        <form method="POST" action="admin.php">
          <input type="text" name="username" placeholder="Usuário" required>
          <input type="password" name="password" placeholder="Senha" required>
          <button type="submit">Entrar</button>
        </form>
      </div>
    </body>
    </html>
    <?php
    exit;
}

$stmt = $db->query("SELECT r.id, r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao, r.oficina, o.descricao as oficina_desc FROM registrations r LEFT JOIN oficinas o ON r.oficina = o.id ORDER BY r.id ASC");
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 1000px; margin: 20px auto; background: #fff; padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #007bff; color: #fff; }
    a { text-decoration: none; color: #007bff; }
    .actions a { margin-right: 5px; }
    .top-menu { margin-bottom: 15px; }
    .top-menu a { margin-right: 15px; font-weight: bold; }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-menu">
      <a href="admin.php">Dashboard</a>
      <a href="index.php">Nova Inscrição</a>
      <a href="admin_reset.php" onclick="return confirm('ATENÇÃO: Ao resetar, todas as inscrições serão apagadas! Continuar?');">Resetar Banco</a>
      <a href="admin_logout.php">Logout</a>
    </div>
    <h2>Lista de Inscrições</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>CPF</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Escola de Atuação</th>
        <th>Área de Atuação</th>
        <th>Oficina</th>
        <th>Ações</th>
      </tr>
      <?php foreach($registrations as $reg): ?>
      <tr>
        <td><?php echo htmlspecialchars($reg['id']); ?></td>
        <td><?php echo htmlspecialchars($reg['nome']); ?></td>
        <td><?php echo htmlspecialchars($reg['cpf']); ?></td>
        <td><?php echo htmlspecialchars($reg['email']); ?></td>
        <td><?php echo htmlspecialchars($reg['telefone']); ?></td>
        <td><?php echo htmlspecialchars($reg['escola']); ?></td>
        <td><?php echo htmlspecialchars($reg['area_atuacao']); ?></td>
        <td><?php echo htmlspecialchars($reg['oficina_desc']); ?></td>
        <td class="actions">
          <a href="admin_edit.php?id=<?php echo $reg['id']; ?>">Editar</a>
          <a href="admin_delete.php?id=<?php echo $reg['id']; ?>" onclick="return confirm('Deseja realmente excluir esta inscrição?');">Excluir</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>
