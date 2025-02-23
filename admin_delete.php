<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
require 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$id) {
    die("ID inválido.");
}

// Carrega a inscrição para verificar a oficina associada
$stmt = $db->prepare("SELECT oficina FROM registrations WHERE id = :id");
$stmt->execute([':id' => $id]);
$reg = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$reg) {
    die("Inscrição não encontrada.");
}

$db->beginTransaction();

// Se houver oficina associada, incrementa a vaga
if($reg['oficina']) {
    $stmt = $db->prepare("UPDATE oficinas SET vagas = vagas + 1 WHERE id = :id");
    $stmt->execute([':id' => $reg['oficina']]);
}

// Exclui a inscrição
$stmt = $db->prepare("DELETE FROM registrations WHERE id = :id");
$stmt->execute([':id' => $id]);

$db->commit();
header("Location: admin.php");
exit;
?>
