<?php
// update_oficinas.php

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

require 'db.php';

/**
 * Função para verificar se uma coluna existe em uma tabela (MySQL/SQLite)
 */
function columnExists(PDO $db, $table, $column) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
        $stmt->execute([':column' => $column]);
        return $stmt->fetch() !== false;
    } else {
        $stmt = $db->query("PRAGMA table_info($table)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if ($col['name'] === $column) {
                return true;
            }
        }
        return false;
    }
}

// Garante que as colunas "identificador" e "total_vagas" existam
if (!columnExists($db, 'oficinas', 'identificador')) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $db->exec("ALTER TABLE oficinas ADD COLUMN identificador INT DEFAULT 0");
    } else {
        $db->exec("ALTER TABLE oficinas ADD COLUMN identificador INTEGER DEFAULT 0");
    }
}
if (!columnExists($db, 'oficinas', 'total_vagas')) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $db->exec("ALTER TABLE oficinas ADD COLUMN total_vagas INT DEFAULT 0");
    } else {
        $db->exec("ALTER TABLE oficinas ADD COLUMN total_vagas INTEGER DEFAULT 0");
    }
}

$message = null;

if (isset($_GET['update_mode'])) {
    $mode = $_GET['update_mode'];
    if (!in_array($mode, ['with_vagas', 'without_vagas'])) {
        $mode = 'without_vagas';
    }

    // Carrega o array de oficinas do arquivo mestre
    $oficinasArquivo = include 'oficinas.php';

    // Prepara SELECT para checar a existência com base no "identificador"
    $stmtSelect = $db->prepare("SELECT id FROM oficinas WHERE identificador = :identificador");

    // Define as queries de UPDATE e INSERT usando "identificador"
    if ($mode === 'with_vagas') {
        $stmtUpdate = $db->prepare("
            UPDATE oficinas
               SET descricao     = :descricao,
                   vagas         = :vagas,
                   total_vagas   = :total_vagas,
                   areas         = :areas,
                   horas         = :horas,
                   escola        = :escola,
                   endereco      = :endereco,
                   identificador = :identificador
             WHERE identificador = :identificador
        ");
    } else {
        $stmtUpdate = $db->prepare("
            UPDATE oficinas
               SET descricao     = :descricao,
                   total_vagas   = :total_vagas,
                   areas         = :areas,
                   horas         = :horas,
                   escola        = :escola,
                   endereco      = :endereco,
                   identificador = :identificador
             WHERE identificador = :identificador
        ");
    }

    $stmtInsert = $db->prepare("
        INSERT INTO oficinas
            (id, descricao, vagas, total_vagas, areas, horas, escola, endereco, identificador)
        VALUES
            (:id, :descricao, :vagas, :total_vagas, :areas, :horas, :escola, :endereco, :identificador)
    ");

    $idsArquivo = [];

    $debugInfo = [];

    $db->beginTransaction();
    try {
        foreach ($oficinasArquivo as $oficina) {
            $idArquivo = (int)$oficina['id'];
            $idsArquivo[] = $idArquivo;
            $descricao = $oficina['descricao'];
            $totalVagasFile = (int)$oficina['vagas']; // Total definido no arquivo
            $areas = $oficina['areas'] ?? '';
            $horas = $oficina['horas'] ?? '';
            $escola = $oficina['escola'] ?? '';
            $endereco = $oficina['endereco'] ?? '';
            $identificador = $idArquivo;

            // Calcula vagas restantes se for o modo com vagas
            $vagasRestantes = $totalVagasFile;
            $inscritos = 0;
            if ($mode === 'with_vagas') {
                $stmtSelect->execute([':identificador' => $identificador]);
                $exists = $stmtSelect->fetch(PDO::FETCH_ASSOC);
                if ($exists) {
                    $stmtRegCount = $db->prepare("SELECT COUNT(*) FROM registrations WHERE oficina = :office_id");
                    $stmtRegCount->execute([':office_id' => $idArquivo]);
                    $inscritos = (int)$stmtRegCount->fetchColumn();
                    $vagasRestantes = max($totalVagasFile - $inscritos, 0);
                }
            }

            $finalVagas = ($mode === 'with_vagas') ? $vagasRestantes : $totalVagasFile;

            // Checa se a oficina já existe (por identificador)
            $stmtSelect->execute([':identificador' => $identificador]);
            $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $params = [
                    ':identificador' => $identificador,
                    ':descricao' => $descricao,
                    ':total_vagas' => $totalVagasFile,
                    ':areas' => $areas,
                    ':horas' => $horas,
                    ':escola' => $escola,
                    ':endereco' => $endereco
                ];
                if ($mode === 'with_vagas') {
                    $params[':vagas'] = $finalVagas;
                }
                $stmtUpdate->execute($params);

                $debugInfo[] = [
                    'id' => $idArquivo,
                    'descricao' => mb_strimwidth($descricao, 0, 60, '...'),
                    'inscritos' => $inscritos,
                    'total_vagas_file' => $totalVagasFile,
                    'vagas_final' => $finalVagas
                ];
            } else {
                $stmtInsert->execute([
                    ':id' => $idArquivo,
                    ':descricao' => $descricao,
                    ':vagas' => $finalVagas,
                    ':total_vagas' => $totalVagasFile,
                    ':areas' => $areas,
                    ':horas' => $horas,
                    ':escola' => $escola,
                    ':endereco' => $endereco,
                    ':identificador' => $identificador
                ]);
                $debugInfo[] = [
                    'id' => $idArquivo,
                    'descricao' => mb_strimwidth($descricao, 0, 60, '...'),
                    'inscritos' => 0,
                    'total_vagas_file' => $totalVagasFile,
                    'vagas_final' => $finalVagas
                ];
            }
        }

        if (count($idsArquivo) > 0) {
            $idsList = implode(',', array_map('intval', $idsArquivo));
            $stmtDelete = $db->prepare("DELETE FROM oficinas WHERE identificador NOT IN ($idsList)");
            $stmtDelete->execute();
        }

        $db->commit();

        $stmtAll = $db->query("SELECT id, vagas FROM oficinas");
        $rows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents('vagas.json', json_encode($rows));

        ob_start();
        echo "Oficinas atualizadas com sucesso!<br>";
        echo "Modo de atualização: <strong>" . htmlspecialchars($mode) . "</strong><br><br>";
        echo "<pre>Detalhes (debug):\n";
        foreach ($debugInfo as $info) {
            echo "ID={$info['id']} | \"{$info['descricao']}\"\n";
            echo "  - Inscritos: {$info['inscritos']}\n";
            echo "  - Total vagas (arquivo): {$info['total_vagas_file']}\n";
            echo "  - Vagas final no banco: {$info['vagas_final']}\n\n";
        }
        echo "</pre>";
        $message = ob_get_clean();

    } catch (Exception $e) {
        $db->rollBack();
        $message = "Erro ao atualizar oficinas: " . $e->getMessage();
    }
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
      text-align: left; 
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
      font-size: 16px; 
      margin-bottom: 20px; 
      color: #333;
      white-space: pre-wrap; 
    }
    h1 { text-align: center; }
    p { text-align: center; }
    .buttons { text-align: center; }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($message): ?>
      <div class="message"><?php echo $message; ?></div>
      <div class="buttons">
        <a href="update_oficinas.php" class="btn">Voltar</a>
      </div>
    <?php else: ?>
      <h1>Atualizar Oficinas</h1>
      <p>Escolha uma opção para atualizar as informações das oficinas:</p>
      <div class="buttons">
        <a href="update_oficinas.php?update_mode=without_vagas" class="btn">Atualizar sem recalcular vagas</a>
        <a href="update_oficinas.php?update_mode=with_vagas" class="btn">Atualizar com recalculo de vagas</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
