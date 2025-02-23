<?php
require 'db.php';
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=relatorio_inscricoes.xls");
header("Pragma: no-cache");
header("Expires: 0");

$stmt = $db->query("SELECT r.nome, r.cpf, r.email, r.telefone, r.escola, r.formacao, r.area_atuacao, 
                    o.descricao as oficina_desc, o.escola as oficina_escola, o.endereco as oficina_endereco
                    FROM registrations r
                    LEFT JOIN oficinas o ON r.oficina = o.id
                    ORDER BY r.id ASC");
?>

<html>
<head>
<meta charset="UTF-8">
<style>
  table { border-collapse: collapse; width: 100%; }
  table, th, td { border: 1px solid #000; }
  th, td { padding: 8px; text-align: left; }
  th { background: #4CAF50; color: white; }
</style>
</head>
<body>
  <h2>Relatório de Inscrições</h2>
  <table>
    <thead>
      <tr>
        <th>Nome</th>
        <th>CPF</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Escola (Usuário)</th>
        <th>Formação</th>
        <th>Área de Atuação</th>
        <th>Oficina</th>
        <th>Escola da Oficina</th>
        <th>Endereço da Oficina</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
      <tr>
        <td><?php echo htmlspecialchars($row['nome']); ?></td>
        <td><?php echo htmlspecialchars($row['cpf']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['telefone']); ?></td>
        <td><?php echo htmlspecialchars($row['escola']); ?></td>
        <td><?php echo htmlspecialchars($row['formacao']); ?></td>
        <td><?php echo htmlspecialchars($row['area_atuacao']); ?></td>
        <td><?php echo htmlspecialchars($row['oficina_desc']); ?></td>
        <td><?php echo htmlspecialchars($row['oficina_escola']); ?></td>
        <td><?php echo htmlspecialchars($row['oficina_endereco']); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</body>
</html>
