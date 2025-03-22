<?php
// get_oficinas.php
header('Content-Type: application/json');
require 'db.php';

/**
 * Normaliza uma string:
 * - Remove espaços extras, converte para minúsculas e, se possível, remove acentos
 */
function normalizeString($str) {
    $str = strtolower(trim($str));
    if (class_exists('Normalizer')) {
        $str = Normalizer::normalize($str, Normalizer::FORM_D);
        $str = preg_replace('/[\p{Mn}]/u', '', $str);
    }
    return $str;
}

// Recebe a área de atuação via GET
$area = isset($_GET['area']) ? trim($_GET['area']) : '';
if (!$area) {
    echo json_encode([]);
    exit;
}
$areaNormalized = normalizeString($area);

// Seleciona todas as oficinas do banco
$stmt = $db->query("SELECT * FROM oficinas");
$oficinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($oficinas as $of) {
    if (!isset($of['areas'])) {
        continue;
    }
    // Explode a string das áreas pelo delimitador "|"
    $areasArrRaw = explode('|', $of['areas']);
    $areasArr = array_map('normalizeString', $areasArrRaw);
    if (in_array($areaNormalized, $areasArr)) {
        $result[] = $of;
    }
}

echo json_encode($result);
