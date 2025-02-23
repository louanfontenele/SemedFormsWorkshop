<?php
require 'db.php';

// Configura os headers para SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

// Vamos enviar atualizações a cada 3 segundos
while (true) {
    // Consulta as vagas de todas as oficinas
    $stmt = $db->query("SELECT id, vagas FROM oficinas");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envia os dados via SSE (um JSON com o array de [id, vagas])
    echo "data: " . json_encode($data) . "\n\n";
    // Força o envio dos dados
    @ob_flush();
    flush();
    // Aguarda 3 segundos antes de enviar novamente
    sleep(3);
}
?>
