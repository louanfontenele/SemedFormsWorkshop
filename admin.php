<?php
session_start();
require 'db.php';

/**
 * Função simples para carregar as variáveis do arquivo .env
 */
function loadEnv($file) {
    $vars = [];
    if(file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($lines as $line) {
            // Ignora comentários
            if(strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $vars[trim($name)] = trim($value);
        }
    }
    return $vars;
}

$env = loadEnv('.env');

// Se o usuário não estiver logado, processa o login
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if ($username === $env['ADMIN_USER'] && $password === $env['ADMIN_PASS']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_role'] = 'admin';
            header("Location: admin.php");
            exit;
        } elseif (isset($env['MOD_USER']) && isset($env['MOD_PASS']) && $username === $env['MOD_USER'] && $password === $env['MOD_PASS']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_role'] = 'mod';
            header("Location: admin.php");
            exit;
        } else {
            $error = "Credenciais inválidas!";
        }
    }
    // Exibe o formulário de login
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
        <?php if (isset($error)) { echo "<p class='error'>{$error}</p>"; } ?>
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

// Usuário logado: define a função de visualização baseada no papel (role)
$role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'admin';

// Se houver um parâmetro GET 'search', utiliza-o para filtrar inscrições
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search !== '') {
    // Busca por nome ou CPF (usando LIKE)
    $stmt = $db->prepare("SELECT r.id, r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao, r.oficina, o.descricao as oficina_desc 
                          FROM registrations r 
                          LEFT JOIN oficinas o ON r.oficina = o.id 
                          WHERE r.nome LIKE :search OR r.cpf LIKE :search 
                          ORDER BY r.id ASC");
    $stmt->execute([':search' => '%' . $search . '%']);
} else {
    $stmt = $db->query("SELECT r.id, r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao, r.oficina, o.descricao as oficina_desc 
                        FROM registrations r 
                        LEFT JOIN oficinas o ON r.oficina = o.id 
                        ORDER BY r.id ASC");
}

$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRegistrations = count($registrations);

// Consulta o total de vagas restantes na tabela de oficinas
$totalVagasRestantes = $db->query("SELECT SUM(vagas) FROM oficinas")->fetchColumn();
// Total de vagas originalmente disponíveis (vagas preenchidas + vagas restantes)
$totalVagasTotais = $totalRegistrations + $totalVagasRestantes;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 95%; margin: 20px auto; background: #fff; padding: 20px; }
    .total-count { margin-bottom: 15px; font-size: 16px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #007bff; color: #fff; }
    a { text-decoration: none; color: #007bff; }
    .actions a { margin-right: 5px; }
    .top-menu { margin-bottom: 15px; }
    .top-menu a { margin-right: 15px; font-weight: bold; }
    .search-form { margin-bottom: 20px; text-align: right; }
    .search-form input[type="text"] { padding: 8px; width: 250px; }
    .search-form button { padding: 8px 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-menu">
      <?php if ($role === 'admin'): ?>
        <a href="admin.php">Dashboard</a>
        <a href="index.php">Nova Inscrição</a>
        <!-- Outros links administrativos, se houver -->
      <?php else: ?>
        <a href="view_registrations.php">Visualizar Inscrições</a>
      <?php endif; ?>
      <a href="admin_logout.php">Logout</a>
    </div>
    <div class="total-count">
      Total de Inscrições: <?php echo $totalRegistrations; ?> |
      Total de Vagas: <?php echo $totalVagasTotais; ?> |
      Vagas Preenchidas: <?php echo $totalRegistrations; ?> |
      Vagas Restantes: <?php echo $totalVagasRestantes; ?>
    </div>
    <div class="search-form">
      <form method="GET" action="admin.php">
        <input type="text" name="search" placeholder="Buscar por Nome ou CPF" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Buscar</button>
        <?php if ($search !== ''): ?>
          <a href="admin.php">Limpar Filtro</a>
        <?php endif; ?>
      </form>
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
        <?php if ($role === 'admin'): ?>
          <th>Ações</th>
        <?php endif; ?>
      </tr>
      <?php foreach ($registrations as $reg): ?>
      <tr>
        <td><?php echo htmlspecialchars($reg['id']); ?></td>
        <td><?php echo htmlspecialchars($reg['nome']); ?></td>
        <td><?php echo htmlspecialchars($reg['cpf']); ?></td>
        <td><?php echo htmlspecialchars($reg['email']); ?></td>
        <td><?php echo htmlspecialchars($reg['telefone']); ?></td>
        <td><?php echo htmlspecialchars($reg['escola']); ?></td>
        <td><?php echo htmlspecialchars($reg['area_atuacao']); ?></td>
        <td><?php echo htmlspecialchars($reg['oficina_desc']); ?></td>
        <?php if ($role === 'admin'): ?>
          <td class="actions">
            <a href="admin_edit.php?id=<?php echo $reg['id']; ?>">Editar</a>
            <a href="admin_delete.php?id=<?php echo $reg['id']; ?>" onclick="return confirm('Deseja realmente excluir esta inscrição?');">Excluir</a>
          </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>
