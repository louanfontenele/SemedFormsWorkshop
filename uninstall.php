<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Função para carregar variáveis do .env
 */
function loadEnv($file) {
    $vars = [];
    if(file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($lines as $line) {
            $line = trim($line);
            if(empty($line) || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $vars[trim($name)] = trim($value);
        }
    }
    return $vars;
}
$env = loadEnv('.env');

// Se o admin não estiver logado, mostra formulário de login
if(!isset($_SESSION['admin_logged_in'])) {
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        // Verifica com as credenciais definidas no .env (ex.: ADMIN_USER e ADMIN_PASS)
        if($username === ($env['ADMIN_USER'] ?? '') && $password === ($env['ADMIN_PASS'] ?? '')) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: uninstall.php");
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
        <title>Uninstall - Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; }
            .container { max-width: 400px; margin: 100px auto; background: #fff; padding: 20px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            input { width: 100%; padding: 10px; margin: 10px 0; }
            button { padding: 10px; width: 100%; background: #007bff; color: #fff; border: none; border-radius: 5px; }
            button:hover { background: #0056b3; }
            .error { color: red; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Admin Login - Uninstall</h2>
            <?php if(isset($error)) echo "<p class='error'>{$error}</p>"; ?>
            <form method="POST" action="uninstall.php">
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

// Se o admin está logado, verifica se o formulário de confirmação foi enviado
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    require 'db.php';
    $config = include 'config.php';
    
    // Remove o arquivo de bloqueio de instalação, se existir
    $lockFile = 'lock-install';
    if(file_exists($lockFile)) {
        unlink($lockFile);
    }
    
    if($config['db_driver'] === 'mysql') {
        try {
            // Exclui os dados das tabelas (não deleta o banco)
            $db->exec("DELETE FROM registrations");
            $db->exec("DELETE FROM oficinas");
            echo "Uninstall realizado com sucesso! Todas as inscrições foram removidas do MySQL.";
        } catch(Exception $e) {
            die("Erro durante o uninstall: " . $e->getMessage());
        }
    } else {
        // Para SQLite: tenta excluir o arquivo do banco
        $db_file = __DIR__ . '/data.db';
        if(file_exists($db_file)) {
            if(unlink($db_file)) {
                echo "Uninstall realizado com sucesso! O arquivo do banco de dados SQLite foi removido.";
            } else {
                // Se não conseguir remover, deleta os dados das tabelas
                try {
                    $db->exec("DELETE FROM registrations");
                    $db->exec("DELETE FROM oficinas");
                    echo "Uninstall realizado com sucesso! Os dados do banco SQLite foram removidos.";
                } catch(Exception $e) {
                    die("Erro durante o uninstall: " . $e->getMessage());
                }
            }
        } else {
            echo "Banco de dados SQLite não encontrado.";
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Uninstall - Remover Dados</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; }
    .container { max-width: 600px; margin: 50px auto; background: #fff; padding: 20px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .btn { background: #dc3545; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none; margin: 10px; }
    .btn:hover { background: #c82333; }
    a { text-decoration: none; color: #007bff; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Uninstall - Remover Dados do Banco</h2>
    <p><strong>Atenção:</strong> Esta ação removerá TODAS as inscrições e dados do banco.<br>
       No MySQL, as tabelas serão esvaziadas; no SQLite, o arquivo do banco poderá ser removido.</p>
    <form method="POST" action="uninstall.php" onsubmit="return confirm('ATENÇÃO: Esta ação removerá TODAS as inscrições! Continuar?');">
      <input type="hidden" name="confirm" value="yes">
      <button type="submit" class="btn">Remover Dados</button>
    </form>
    <p><a href="admin.php">Voltar ao Painel de Administração</a></p>
  </div>
</body>
</html>
