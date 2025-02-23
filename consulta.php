<?php
// consulta.php
header('Content-Type: application/json');
require 'db.php';

// Recebe o CPF com pontuação (ex.: "000.000.000-00")
$cpf = isset($_GET['cpf']) ? trim($_GET['cpf']) : '';

// Se estiver vazio ou muito curto, devolve found=false
if(strlen($cpf) < 14) {
    echo json_encode(['found' => false]);
    exit;
}

// Faz a busca literal
$stmt = $db->prepare("SELECT r.nome, r.cpf, r.email, r.telefone, r.escola, r.formacao, r.area_atuacao,
                             o.descricao as oficina_desc, o.escola as oficina_escola, o.endereco as oficina_endereco
                      FROM registrations r
                      LEFT JOIN oficinas o ON r.oficina = o.id
                      WHERE r.cpf = :cpf");
$stmt->execute([':cpf' => $cpf]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($row) {
    echo json_encode([
        'found' => true,
        'nome' => $row['nome'],
        'cpf' => $row['cpf'],
        'email' => $row['email'],
        'telefone' => $row['telefone'],
        'escola' => $row['escola'],
        'formacao' => $row['formacao'],
        'area_atuacao' => $row['area_atuacao'],
        'oficina_desc' => $row['oficina_desc'],
        'oficina_escola' => $row['oficina_escola'],
        'oficina_endereco' => $row['oficina_endereco']
    ]);
} else {
    echo json_encode(['found' => false]);
}
