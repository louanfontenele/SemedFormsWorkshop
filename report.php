<?php
require 'db.php';

$stmt = $db->query("SELECT r.nome, r.cpf, r.email, r.telefone, r.escola, r.formacao, r.area_atuacao, 
                    o.descricao as oficina_desc, o.escola as oficina_escola, o.endereco as oficina_endereco
                    FROM registrations r
                    LEFT JOIN oficinas o ON r.oficina = o.id
                    ORDER BY r.id ASC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Inscrições</title>
  <style>
    body { font-family: Arial, sans-serif; margin:0; padding:0; background: #f4f4f4; }
    h2 { margin: 20px; }
    .buttons { margin: 20px; }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #ddd; }
    th, td { padding: 10px; text-align: left; }
    th { background: #007bff; color: #fff; }
    .btn { background: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; text-decoration: none; margin-right: 10px; }
    .btn:hover { background: #0056b3; }
  </style>
</head>
<body>
  <h2>Relatório de Inscrições</h2>
  <div class="buttons">
    <button class="btn" onclick="window.print()">Imprimir</button>
    <a href="export_excel.php" class="btn">Exportar para Excel</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Nome</th>
        <th>CPF</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Escola de Atuação</th>
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
