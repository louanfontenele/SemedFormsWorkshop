<?php
// welcome.php
$config = include 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bem-vindo - Oficinas SEMED</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 800px; margin: 50px auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
    .btn { background: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none; }
    .btn:hover { background: #0056b3; }
    footer { margin-top: 20px; font-size: 12px; color: #777; }
    pre { text-align: left; background: #eee; padding: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Bem-vindo!</h1>
    <p><?php echo nl2br(htmlspecialchars($config['welcome_message'])); ?></p>
    <p><strong>Período de Inscrições:</strong> <?php echo date("d/m/Y H:i", strtotime($config['registration_start'])); ?> até <?php echo date("d/m/Y H:i", strtotime($config['registration_end'])); ?></p>
    <pre><strong>Contato:</strong>
<?php echo htmlspecialchars($config['contact_info']); ?></pre>
    <a href="index.php" class="btn">Iniciar Inscrição</a>
  </div>
  <?php include "footer.php"; ?>
</body>
</html>
