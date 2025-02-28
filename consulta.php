<?php
// consulta.php
header('Content-Type: application/json');
require 'db.php';
$config = include 'config.php';

$cpf = isset($_GET['cpf']) ? trim($_GET['cpf']) : '';
if(strlen($cpf) < 14) {
    echo json_encode(['found' => false]);
    exit;
}

$stmt = $db->prepare("SELECT r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao,
                             o.descricao as oficina_desc, o.escola as oficina_escola, o.endereco as oficina_endereco
                      FROM registrations r
                      LEFT JOIN oficinas o ON r.oficina = o.id
                      WHERE r.cpf = :cpf");
$stmt->execute([':cpf' => $cpf]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($row) {
    // CPF sem pontuação
    $cpfNumeric = preg_replace('/\D/', '', $row['cpf']);
    echo json_encode([
        'found'             => true,
        'cpf_numeric'       => $cpfNumeric,
        'nome'              => $row['nome'],
        'cpf'               => $row['cpf'],
        'email'             => $row['email'],
        'telefone'          => $row['telefone'],
        'escola'            => $row['escola'],
        'area_atuacao'      => $row['area_atuacao'],
        'oficina_desc'      => $row['oficina_desc'],
        'oficina_escola'    => $row['oficina_escola'],
        'oficina_endereco'  => $row['oficina_endereco'],
        'opening_address'   => $config['opening_address'],
        'contact_info'      => $config['contact_info']
    ]);
} else {
    echo json_encode(['found' => false]);
}
?>
