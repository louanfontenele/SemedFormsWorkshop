<?php
// update_vagas.php
require 'db.php';

// Consulta todas as oficinas (id e vagas) diretamente do banco
$stmt = $db->query("SELECT id, vagas FROM oficinas");
$oficinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($oficinas === false) {
    die("Erro ao recuperar dados das oficinas.");
}

// Atualiza o arquivo vagas.json com os dados atuais (formatado para melhor leitura)
$resultado = json_encode($oficinas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents('vagas.json', $resultado) === false) {
    die("Erro ao atualizar o arquivo vagas.json.");
}

echo "vagas.json atualizado com sucesso.";
?>
