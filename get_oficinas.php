<?php
// get_oficinas.php
header('Content-Type: application/json');
require 'db.php';

$area = isset($_GET['area']) ? trim($_GET['area']) : '';
if(!$area) {
    echo json_encode([]);
    exit;
}

// Seleciona todas as oficinas (ou só com vagas > 0, mas aqui mantemos todas para exibir desabilitadas)
$stmt = $db->query("SELECT * FROM oficinas");
$oficinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach($oficinas as $of) {
    $areasArr = explode('|', $of['areas'] ?? '');
    if(in_array($area, $areasArr)) {
        $result[] = $of;
    }
}

echo json_encode($result);
