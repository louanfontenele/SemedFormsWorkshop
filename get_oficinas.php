<?php
require 'db.php';
header('Content-Type: application/json');

// Lê a área enviada via GET
$area = isset($_GET['area']) ? trim($_GET['area']) : '';
if (!$area) {
    echo json_encode([]);
    exit;
}

// Consulta no banco as oficinas com vagas disponíveis
$stmt = $db->query("SELECT * FROM oficinas WHERE vagas > 0");
$oficinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($oficinas as $of) {
    // No banco, o campo que indica as áreas é "areas" (com um ou mais valores separados por "|")
    $areasArr = explode('|', $of['areas']);
    // Se a área selecionada estiver entre as áreas que a oficina atende, inclui no resultado
    if (in_array($area, $areasArr)) {
        $result[] = $of;
    }
}

echo json_encode($result);
?>
