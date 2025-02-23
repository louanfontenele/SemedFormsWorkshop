<?php
// check_cpf.php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require 'db.php';
header('Content-Type: application/json');

$cpf = isset($_GET['cpf']) ? trim($_GET['cpf']) : '';
if(!$cpf) {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $db->prepare("SELECT id FROM registrations WHERE cpf = :cpf");
$stmt->execute([':cpf' => $cpf]);
$exists = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
echo json_encode(['exists' => $exists]);
?>
