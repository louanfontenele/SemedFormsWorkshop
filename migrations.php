<?php
// migrations.php
require 'db.php';

/**
 * Verifica se uma coluna existe na tabela.
 *
 * @param PDO    $db    A conexão com o banco de dados.
 * @param string $table O nome da tabela.
 * @param string $column O nome da coluna.
 * @return bool True se existir, false caso contrário.
 */
function columnExists(PDO $db, $table, $column) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $stmt = $db->prepare("SHOW COLUMNS FROM $table LIKE :column");
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

$output = "";

// Verifica e adiciona a coluna 'identificador' se necessário
if (!columnExists($db, 'oficinas', 'identificador')) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $db->exec("ALTER TABLE oficinas ADD COLUMN identificador INT DEFAULT 0");
        $output .= "Coluna 'identificador' adicionada na tabela 'oficinas' (MySQL).<br>";
    } else {
        $db->exec("ALTER TABLE oficinas ADD COLUMN identificador INTEGER DEFAULT 0");
        $output .= "Coluna 'identificador' adicionada na tabela 'oficinas' (SQLite).<br>";
    }
} else {
    $output .= "Coluna 'identificador' já existe na tabela 'oficinas'.<br>";
}

// Verifica e adiciona a coluna 'total_vagas' se necessário
if (!columnExists($db, 'oficinas', 'total_vagas')) {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $db->exec("ALTER TABLE oficinas ADD COLUMN total_vagas INT DEFAULT 0");
        $output .= "Coluna 'total_vagas' adicionada na tabela 'oficinas' (MySQL).<br>";
    } else {
        $db->exec("ALTER TABLE oficinas ADD COLUMN total_vagas INTEGER DEFAULT 0");
        $output .= "Coluna 'total_vagas' adicionada na tabela 'oficinas' (SQLite).<br>";
    }
} else {
    $output .= "Coluna 'total_vagas' já existe na tabela 'oficinas'.<br>";
}

// Atualiza a coluna 'identificador' com os dados do arquivo oficinas.php
$oficinasArquivo = include 'oficinas.php';
foreach ($oficinasArquivo as $oficina) {
    $idArquivo = (int)$oficina['id'];
    $stmtUpdateIdentificador = $db->prepare("UPDATE oficinas SET identificador = :identificador WHERE id = :id");
    $stmtUpdateIdentificador->execute([
        ':identificador' => $idArquivo,
        ':id'            => $idArquivo
    ]);
}
$output .= "Coluna 'identificador' atualizada com dados do arquivo oficinas.php.<br>";

// Opcional: atualiza a coluna 'total_vagas' para cada oficina com base no arquivo mestre
foreach ($oficinasArquivo as $oficina) {
    $idArquivo = (int)$oficina['id'];
    $totalVagas = (int)$oficina['vagas'];
    $stmtUpdateTotal = $db->prepare("UPDATE oficinas SET total_vagas = :total_vagas WHERE id = :id");
    $stmtUpdateTotal->execute([
        ':total_vagas' => $totalVagas,
        ':id'          => $idArquivo
    ]);
}
$output .= "Coluna 'total_vagas' atualizada com os valores do arquivo oficinas.php.<br>";

// Exibe o resultado da migração
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Migration Script</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Migration Result</h1>
        <p><?php echo $output; ?></p>
    </div>
</body>
</html>
