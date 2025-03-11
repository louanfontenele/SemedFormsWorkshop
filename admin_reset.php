<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Apaga todas as inscrições
    $db->exec("DELETE FROM registrations");

    // Se desejar resetar as vagas para seus valores originais, adicione aqui um UPDATE para cada oficina.
    // Exemplo: $db->exec("UPDATE oficinas SET vagas = 35");

    // Remove o arquivo de bloqueio de instalação, se existir
    if(file_exists("lock-install")) {
        unlink("lock-install");
    }

    // Atualiza vagas.json para refletir a situação atual das oficinas
    try {
        $stmt = $db->query("SELECT id, vagas FROM oficinas");
        $vagasSnapshot = $stmt->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents('vagas.json', json_encode($vagasSnapshot));
    } catch(Exception $e) {
        error_log("Erro ao atualizar vagas.json após admin_reset: " . $e->getMessage());
    }

    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Resetar Banco</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; }
    .container { max-width: 500px; margin: 100px auto; background: #fff; padding: 20px; text-align: center; }
    button { padding: 10px 20px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #c82333; }
    a { text-decoration: none; color: #007bff; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Resetar Banco de Inscrições</h2>
    <p>A ação de resetar apagará TODAS as inscrições do banco.<br>Você deseja continuar?</p>
    <form method="POST" action="admin_reset.php">
      <button type="submit" onclick="return confirm('ATENÇÃO: Esta ação apagará TODAS as inscrições! Continuar?');">Sim, resetar</button>
    </form>
    <p><a href="admin.php">Cancelar</a></p>
  </div>
</body>
</html>
