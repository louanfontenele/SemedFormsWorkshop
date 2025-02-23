<?php
require 'db.php';

// Verifica se o parâmetro update_mode foi enviado
if (isset($_GET['update_mode'])) {
    $mode = $_GET['update_mode'];
    // Valida o modo; se inválido, assume "without_vagas"
    if (!in_array($mode, ['with_vagas', 'without_vagas'])) {
        $mode = 'without_vagas';
    }
    
    // Carrega o array atualizado de oficinas do arquivo
    $oficinasArquivo = include 'oficinas.php';
    
    // Prepara uma declaração SELECT para verificar se a oficina já existe
    $stmtSelect = $db->prepare("SELECT vagas FROM oficinas WHERE id = :id");
    
    // Prepara a declaração de UPDATE com base no modo
    if ($mode === 'with_vagas') {
        $stmtUpdate = $db->prepare("UPDATE oficinas 
            SET descricao = :descricao,
                vagas = :vagas,
                areas = :areas,
                horas = :horas,
                escola = :escola,
                endereco = :endereco
            WHERE id = :id");
    } else {
        // Atualiza apenas os dados informativos, sem alterar as vagas
        $stmtUpdate = $db->prepare("UPDATE oficinas 
            SET descricao = :descricao,
                areas = :areas,
                horas = :horas,
                escola = :escola,
                endereco = :endereco
            WHERE id = :id");
    }
    
    // Prepara a declaração de INSERT para oficinas novas
    $stmtInsert = $db->prepare("INSERT INTO oficinas (id, descricao, vagas, areas, horas, escola, endereco) 
        VALUES (:id, :descricao, :vagas, :areas, :horas, :escola, :endereco)");
    
    // Array para armazenar os IDs presentes no arquivo
    $idsArquivo = [];
    
    $db->beginTransaction();
    
    foreach ($oficinasArquivo as $oficina) {
        $idsArquivo[] = $oficina['id'];
        
        // Checa se a oficina já existe no banco
        $stmtSelect->execute([':id' => $oficina['id']]);
        $existe = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        
        if ($existe) {
            // Prepara os parâmetros para UPDATE
            $params = [
                ':id' => $oficina['id'],
                ':descricao' => $oficina['descricao'],
                ':areas' => $oficina['areas'],
                ':horas' => $oficina['horas'],
                ':escola' => $oficina['escola'],
                ':endereco' => $oficina['endereco']
            ];
            if ($mode === 'with_vagas') {
                $params[':vagas'] = $oficina['vagas']; // Reseta as vagas conforme o arquivo
            }
            $stmtUpdate->execute($params);
        } else {
            // Insere a nova oficina
            $stmtInsert->execute([
                ':id' => $oficina['id'],
                ':descricao' => $oficina['descricao'],
                ':vagas' => $oficina['vagas'],
                ':areas' => $oficina['areas'],
                ':horas' => $oficina['horas'],
                ':escola' => $oficina['escola'],
                ':endereco' => $oficina['endereco']
            ]);
        }
    }
    
    // (Opcional) Exclui do banco as oficinas que não estão mais no arquivo
    if (count($idsArquivo) > 0) {
        $idsList = implode(',', array_map('intval', $idsArquivo));
        $stmtDelete = $db->prepare("DELETE FROM oficinas WHERE id NOT IN ($idsList)");
        $stmtDelete->execute();
    }
    
    $db->commit();
    
    $message = "Oficinas atualizadas com sucesso!<br>Modo de atualização: " . htmlspecialchars($mode);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Atualizar Oficinas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { 
      font-family: Arial, sans-serif; 
      background: #f4f4f4; 
      margin: 0; 
      padding: 0; 
    }
    .container { 
      max-width: 800px; 
      margin: 50px auto; 
      background: #fff; 
      padding: 20px; 
      box-shadow: 0 0 10px rgba(0,0,0,0.1); 
      text-align: center; 
    }
    .btn { 
      background: #007bff; 
      color: #fff; 
      border: none; 
      padding: 10px 20px; 
      border-radius: 5px; 
      font-size: 16px; 
      cursor: pointer; 
      text-decoration: none; 
      margin: 10px; 
      display: inline-block; 
    }
    .btn:hover { 
      background: #0056b3; 
    }
    .message { 
      font-size: 18px; 
      margin-bottom: 20px; 
    }
  </style>
</head>
<body>
  <div class="container">
    <?php if(isset($message)): ?>
      <div class="message"><?php echo $message; ?></div>
      <a href="update_oficinas.php" class="btn">Voltar</a>
    <?php else: ?>
      <h1>Atualizar Oficinas</h1>
      <p>Escolha uma opção para atualizar as informações das oficinas:</p>
      <a href="update_oficinas.php?update_mode=without_vagas" class="btn">Atualizar sem resetar vagas</a>
      <a href="update_oficinas.php?update_mode=with_vagas" class="btn">Atualizar com reset de vagas</a>
    <?php endif; ?>
  </div>
</body>
</html>
