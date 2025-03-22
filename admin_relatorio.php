<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

require 'db.php';
$config = include 'config.php';

// 1. Consulta todas as oficinas (para exibição detalhada por oficina)
$stmtOffices = $db->query("SELECT id, descricao FROM oficinas ORDER BY id ASC");
$offices = $stmtOffices->fetchAll(PDO::FETCH_ASSOC);

// 2. Consulta o agrupamento de inscrições por oficina e área
$stmtGroup = $db->query("SELECT oficina, area_atuacao, COUNT(*) AS total 
                          FROM registrations 
                          GROUP BY oficina, area_atuacao");
$grouped = $stmtGroup->fetchAll(PDO::FETCH_ASSOC);
// Organiza os resultados em um array: chave = id da oficina, valor = array( área => total )
$officeData = [];
foreach ($grouped as $row) {
    $ofId = $row['oficina'];
    $area = $row['area_atuacao'];
    $total = $row['total'];
    $officeData[$ofId][$area] = $total;
}

// 3. Consulta o total de inscrições por área (resumo geral)
$stmtAreas = $db->query("SELECT area_atuacao, COUNT(*) AS total 
                          FROM registrations 
                          GROUP BY area_atuacao 
                          ORDER BY area_atuacao ASC");
$areasSummary = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);

// 4. Consulta o total de inscrições por escola da oficina (agrupado pelo campo "escola" em oficinas)
// Aqui assumimos que toda inscrição tem oficina e que o campo 'escola' da tabela oficinas guarda o nome da escola onde a oficina é realizada.
$stmtSchool = $db->query("SELECT o.escola AS oficina_escola, COUNT(*) AS total 
                           FROM registrations r
                           LEFT JOIN oficinas o ON r.oficina = o.id
                           GROUP BY o.escola
                           ORDER BY o.escola ASC");
$schoolsSummary = $stmtSchool->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Relatório de Inscrições</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Layout geral */
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 1000px;
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1, h2, h3 {
      text-align: center;
    }
    /* Abas (tabs) */
    .tabs {
      display: flex;
      margin-bottom: 20px;
      border-bottom: 2px solid #007bff;
    }
    .tab {
      padding: 10px 20px;
      cursor: pointer;
      border: 1px solid #007bff;
      border-bottom: none;
      margin-right: 5px;
      background: #007bff;
      color: #fff;
      border-radius: 5px 5px 0 0;
    }
    .tab.active {
      background: #fff;
      color: #007bff;
      font-weight: bold;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th, td {
      padding: 8px;
      text-align: left;
    }
    th {
      background: #007bff;
      color: #fff;
    }
    .back-btn {
      display: inline-block;
      margin: 10px 0;
      padding: 10px 15px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }
  </style>
  <script>
    // Função para alternar abas
    function showTab(index) {
      var tabs = document.getElementsByClassName('tab');
      var contents = document.getElementsByClassName('tab-content');
      for (var i = 0; i < tabs.length; i++) {
        if (i === index) {
          tabs[i].classList.add('active');
          contents[i].classList.add('active');
        } else {
          tabs[i].classList.remove('active');
          contents[i].classList.remove('active');
        }
      }
    }
    window.onload = function() {
      showTab(0); // Exibe a primeira aba por padrão
    }
  </script>
</head>
<body>
  <div class="container">
    <h1>Relatório de Inscrições</h1>
    <div class="tabs">
      <div class="tab" onclick="showTab(0)">Inscrições por Oficina</div>
      <div class="tab" onclick="showTab(1)">Resumo por Área</div>
      <div class="tab" onclick="showTab(2)">Inscrições por Escola da Oficina</div>
    </div>
    
    <!-- Tab 1: Inscrições por Oficina (Detalhado) -->
    <div class="tab-content" id="tab1">
      <h2>Inscrições por Oficina (Detalhado)</h2>
      <?php if(empty($offices)): ?>
        <p>Nenhuma oficina encontrada.</p>
      <?php else: ?>
        <?php foreach ($offices as $office):
            $ofId = $office['id'];
            $desc = $office['descricao'];
            // Dados agrupados por área para esta oficina
            $data = isset($officeData[$ofId]) ? $officeData[$ofId] : [];
        ?>
          <h3>Oficina: <?php echo htmlspecialchars($desc); ?> (ID: <?php echo $ofId; ?>)</h3>
          <?php if(empty($data)): ?>
            <p>Nenhuma inscrição para esta oficina.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Área de Atuação</th>
                  <th>Número de Inscrições</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($data as $area => $count): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($area); ?></td>
                    <td><?php echo $count; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <!-- Tab 2: Resumo Geral por Área -->
    <div class="tab-content" id="tab2">
      <h2>Resumo Geral de Inscrições por Área</h2>
      <?php if(empty($areasSummary)): ?>
        <p>Nenhuma inscrição encontrada.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Área de Atuação</th>
              <th>Total de Inscrições</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($areasSummary as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['area_atuacao']); ?></td>
                <td><?php echo $row['total']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    
    <!-- Tab 3: Inscrições por Escola da Oficina -->
    <div class="tab-content" id="tab3">
      <h2>Inscrições por Escola da Oficina</h2>
      <?php if(empty($schoolsSummary)): ?>
        <p>Nenhuma inscrição encontrada.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Escola da Oficina</th>
              <th>Total de Inscrições</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($schoolsSummary as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['oficina_escola']); ?></td>
                <td><?php echo $row['total']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    
    <a href="admin.php" class="back-btn">Voltar ao Dashboard</a>
  </div>
</body>
</html>
