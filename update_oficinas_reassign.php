<?php
// update_oficinas_reassign.php

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

require 'db.php';

/**
 * Função para verificar se uma coluna existe na tabela
 * Suporta MySQL e SQLite.
 */
function hasColumn(PDO $db, $table, $column) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $stmt = $db->prepare("SHOW COLUMNS FROM $table LIKE :column");
        $stmt->execute([':column' => $column]);
        return $stmt->fetch() !== false;
    } elseif ($driver === 'sqlite') {
        $stmt = $db->query("PRAGMA table_info($table)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if ($col['name'] === $column) {
                return true;
            }
        }
        return false;
    }
    return false;
}

// Adiciona a coluna "identificador" se não existir
if (!hasColumn($db, 'oficinas', 'identificador')) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $db->exec("ALTER TABLE oficinas ADD COLUMN identificador INT NOT NULL DEFAULT 0");
    } else {
        $db->exec("ALTER TABLE oficinas ADD COLUMN identificador INTEGER DEFAULT 0");
    }
}

// (Opcional) Se desejar controlar também o total de vagas definidas no arquivo,
// pode ser adicionada a coluna "total_vagas". Caso já esteja implementada, ignore.
// if (!hasColumn($db, 'oficinas', 'total_vagas')) {
//     $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
//     if ($driver === 'mysql') {
//         $db->exec("ALTER TABLE oficinas ADD COLUMN total_vagas INT NOT NULL DEFAULT 0");
//     } else {
//         $db->exec("ALTER TABLE oficinas ADD COLUMN total_vagas INTEGER DEFAULT 0");
//     }
// }

// Carrega o arquivo mestre de oficinas
$oficinasArquivo = include 'oficinas.php';

// Array para armazenar os identificadores (do arquivo mestre)
$fileIdentifiers = [];

// Inicia a transação
$db->beginTransaction();

// Atualiza (ou insere) as oficinas com base no arquivo mestre
foreach ($oficinasArquivo as $oficina) {
    $identificador = (int)$oficina['id'];
    $fileIdentifiers[] = $identificador;
    
    // Verifica se já existe uma oficina com este identificador
    $stmtSelect = $db->prepare("SELECT id FROM oficinas WHERE identificador = :identificador");
    $stmtSelect->execute([':identificador' => $identificador]);
    $existing = $stmtSelect->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Atualiza os dados informativos (não altera o campo 'vagas' para preservar inscrições)
        $stmtUpdate = $db->prepare("UPDATE oficinas 
            SET descricao = :descricao,
                areas = :areas,
                horas = :horas,
                escola = :escola,
                endereco = :endereco,
                identificador = :identificador
            WHERE identificador = :identificador");
        $stmtUpdate->execute([
            ':descricao'     => $oficina['descricao'],
            ':areas'         => $oficina['areas'],
            ':horas'         => $oficina['horas'],
            ':escola'        => $oficina['escola'],
            ':endereco'      => $oficina['endereco'],
            ':identificador' => $identificador
        ]);
    } else {
        // Insere nova oficina (o campo primary key id é gerado automaticamente)
        $stmtInsert = $db->prepare("INSERT INTO oficinas (descricao, vagas, areas, horas, escola, endereco, identificador) 
            VALUES (:descricao, :vagas, :areas, :horas, :escola, :endereco, :identificador)");
        $stmtInsert->execute([
            ':descricao'     => $oficina['descricao'],
            ':vagas'         => (int)$oficina['vagas'],
            ':areas'         => $oficina['areas'],
            ':horas'         => $oficina['horas'],
            ':escola'        => $oficina['escola'],
            ':endereco'      => $oficina['endereco'],
            ':identificador' => $identificador
        ]);
    }
}

// Cria uma lista (string) de identificadores do arquivo
$idsList = implode(',', array_map('intval', $fileIdentifiers));

// Seleciona as oficinas que estão no banco mas não constam mais no arquivo mestre
$stmtDelete = $db->prepare("SELECT id, identificador, descricao FROM oficinas WHERE identificador NOT IN ($idsList)");
$stmtDelete->execute();
$officesToDelete = $stmtDelete->fetchAll(PDO::FETCH_ASSOC);

// Array para armazenar registros (inscrições) órfãos que não puderam ser reatribuídos automaticamente
$orphanRegistrations = [];

foreach ($officesToDelete as $office) {
    $oldId = $office['id'];
    $desc = $office['descricao'];
    
    // Procura por outra oficina (ainda existente) com a mesma descrição
    $stmtFind = $db->prepare("SELECT id FROM oficinas WHERE descricao = :desc AND identificador IN ($idsList) ORDER BY identificador ASC LIMIT 1");
    $stmtFind->execute([':desc' => $desc]);
    $newOffice = $stmtFind->fetch(PDO::FETCH_ASSOC);
    
    if ($newOffice) {
        // Atualiza as inscrições para usar o novo office id
        $stmtReassign = $db->prepare("UPDATE registrations SET oficina = :newId WHERE oficina = :oldId");
        $stmtReassign->execute([':newId' => $newOffice['id'], ':oldId' => $oldId]);
    } else {
        // Se não houver uma oficina de mesma descrição para reatribuir, armazena os registros órfãos
        $stmtOrphan = $db->prepare("SELECT id, nome, cpf FROM registrations WHERE oficina = :oldId");
        $stmtOrphan->execute([':oldId' => $oldId]);
        $orphans = $stmtOrphan->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($orphans)) {
            $orphanRegistrations = array_merge($orphanRegistrations, $orphans);
        }
    }
    
    // Exclui a oficina que não consta mais no arquivo mestre
    $stmtDel = $db->prepare("DELETE FROM oficinas WHERE id = :id");
    $stmtDel->execute([':id' => $oldId]);
}

// (Opcional) Aqui você pode atualizar outras informações se necessário

$db->commit();

// Atualiza o snapshot de vagas (vagas.json) a partir do banco
$stmtSnapshot = $db->query("SELECT id, vagas FROM oficinas");
$vagasSnapshot = $stmtSnapshot->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('vagas.json', json_encode($vagasSnapshot));

// Prepara a mensagem de saída
$message = "Oficinas atualizadas com sucesso e os registros foram remanejados conforme o arquivo mestre.<br>";
if (!empty($orphanRegistrations)) {
    $message .= "As seguintes inscrições ficaram órfãs e precisam de reatribuição manual:";
    $message .= "<ul>";
    foreach ($orphanRegistrations as $orphan) {
        $message .= "<li>ID: " . htmlspecialchars($orphan['id']) . " - Nome: " . htmlspecialchars($orphan['nome']) . " - CPF: " . htmlspecialchars($orphan['cpf']) . "</li>";
    }
    $message .= "</ul>";
} else {
    $message .= "Todos os registros foram reatribuídos corretamente.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Atualizar Oficinas - Remanejamento</title>
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
    ul { list-style: none; padding: 0; }
    li { text-align: left; margin: 5px 0; }
  </style>
</head>
<body>
  <div class="container">
    <div class="message"><?php echo $message; ?></div>
    <a href="update_oficinas.php" class="btn">Voltar</a>
  </div>
</body>
</html>
