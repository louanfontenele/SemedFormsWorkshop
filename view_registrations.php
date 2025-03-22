<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

// Se desejar, você pode restringir para somente moderadores:
// if ($_SESSION['admin_role'] !== 'mod') {
//     header("Location: admin.php");
//     exit;
// }

require 'db.php';
$config = include 'config.php';

// Recupera o termo de busca (por nome ou CPF) se houver
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepara a consulta de inscrições
if ($search !== '') {
    // Procura em nome e CPF (usando LIKE)
    $stmt = $db->prepare("SELECT r.id, r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao, r.oficina, o.descricao AS oficina_desc 
                          FROM registrations r 
                          LEFT JOIN oficinas o ON r.oficina = o.id 
                          WHERE r.nome LIKE :search OR r.cpf LIKE :search 
                          ORDER BY r.id ASC");
    $stmt->execute([':search' => '%' . $search . '%']);
} else {
    $stmt = $db->query("SELECT r.id, r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao, r.oficina, o.descricao AS oficina_desc 
                        FROM registrations r 
                        LEFT JOIN oficinas o ON r.oficina = o.id 
                        ORDER BY r.id ASC");
}
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRegistrations = count($registrations);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Visualização de Inscrições - <?php echo htmlspecialchars($config['event_name']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 95%; margin: 20px auto; background: #fff; padding: 20px; }
    h1 { text-align: center; }
    .search-form { margin-bottom: 20px; text-align: center; }
    .search-form input[type="text"] { padding: 8px; width: 300px; }
    .search-form button { padding: 8px 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #007bff; color: #fff; }
    .back-btn { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Visualização de Inscrições (Somente Leitura)</h1>
    <p>Total de Inscrições: <?php echo $totalRegistrations; ?></p>
    <div class="search-form">
      <form method="GET" action="view_registrations.php">
        <input type="text" name="search" placeholder="Buscar por Nome ou CPF" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Buscar</button>
        <?php if ($search !== ''): ?>
          <a href="view_registrations.php">Limpar Filtro</a>
        <?php endif; ?>
      </form>
    </div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>CPF</th>
          <th>Email</th>
          <th>Telefone</th>
          <th>Escola de Atuação</th>
          <th>Área de Atuação</th>
          <th>Oficina</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($registrations)): ?>
          <tr>
            <td colspan="8" style="text-align: center;">Nenhuma inscrição encontrada.</td>
          </tr>
        <?php else: ?>
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
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <div style="text-align: center;">
      <a href="admin_logout.php" class="back-btn">Logout</a>
    </div>
  </div>
</body>
</html>
