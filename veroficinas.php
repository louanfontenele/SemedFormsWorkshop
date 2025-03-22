<?php
// veroficinas.php
$config = include 'config.php';
$areas = include 'areas.php';
require 'db.php';

// Calcula os totais globais
$totalRegistrations = $db->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$totalVagasRestantes = $db->query("SELECT SUM(vagas) FROM oficinas")->fetchColumn();
// Como não utilizamos a coluna total_vagas para o total global, definimos:
$totalVagasTotais = $totalRegistrations + $totalVagasRestantes;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Oficinas - <?php echo htmlspecialchars($config['event_name']); ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    h1 {
      color: #333;
    }
    label {
      display: block;
      margin: 10px 0 5px;
      font-weight: bold;
    }
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-bottom: 15px;
      box-sizing: border-box;
    }
    .oficina-item {
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 5px;
      text-align: left;
      word-wrap: break-word;
    }
    .btn {
      background: #007bff;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      text-decoration: none;
      margin: 10px;
      display: inline-block;
    }
    .btn:hover {
      background: #0056b3;
    }
    .totals {
      margin-top: 20px;
      font-size: 16px;
      font-weight: bold;
    }
  </style>
  <script>
    function loadOficinasConsulta() {
      var area = document.getElementById('area_atuacao').value;
      var container = document.getElementById('oficinasContainer');
      container.innerHTML = 'Carregando oficinas...';
      
      // Realiza duas requisições: uma para obter os dados das oficinas e outra para o snapshot de vagas
      Promise.all([
          fetch('get_oficinas.php?area=' + encodeURIComponent(area)).then(response => response.json()),
          fetch('vagas.json').then(response => response.json())
      ])
      .then(function(results) {
          var oficinasData = results[0];
          var vagasData = results[1];
          
          // Cria um mapa de vagas restantes: chave = id da oficina, valor = vagas restantes
          var vagasMap = {};
          vagasData.forEach(function(item) {
              vagasMap[item.id] = item.vagas;
          });
          
          if (!oficinasData || oficinasData.length === 0) {
              container.innerHTML = '<p>Nenhuma oficina encontrada para esta área.</p>';
              return;
          }
          
          var html = '';
          oficinasData.forEach(function(of) {
              // Usa o campo "identificador" se existir; caso contrário, usa o id
              var identificador = (of.identificador !== undefined && of.identificador !== null) ? of.identificador : of.id;
              
              // Obtenha o número de vagas restantes do snapshot, se disponível
              var vagasRestantes = (vagasMap[of.id] !== undefined) ? vagasMap[of.id] : 'N/A';
              // Total de vagas conforme definido no arquivo mestre (campo "total_vagas"); se não existir, exibe "N/A"
              var vagasTotais = (of.total_vagas !== undefined) ? of.total_vagas : 'N/A';
              
              // Define o estilo da borda conforme as vagas restantes: verde se > 0; vermelha se 0; padrão caso contrário
              var borderStyle = "";
              if (typeof vagasRestantes === "number") {
                  if (vagasRestantes > 0) {
                      borderStyle = "border: 3px solid green;";
                  } else {
                      borderStyle = "border: 3px solid red;";
                  }
              } else {
                  borderStyle = "border: 1px solid #ddd;";
              }
              
              html += '<div class="oficina-item" style="' + borderStyle + '">';
              html += '<strong> ' + of.descricao + '</strong><br>';
              html += 'Horário: ' + of.horas + '<br>';
              if (of.escola) {
                  html += 'Escola: ' + of.escola + '<br>';
              }
              if (of.endereco) {
                  html += 'Endereço: ' + of.endereco + '<br>';
              }
              html += 'Vagas Totais: ' + vagasTotais + ' | Vagas Restantes: ' + vagasRestantes + '<br>';
              html += 'Identificador: ' + identificador + '<br>';
              html += '</div>';
          });
          container.innerHTML = html;
      })
      .catch(function(error) {
          console.error(error);
          container.innerHTML = '<p style="color:red;">Erro ao carregar oficinas.</p>';
      });
    }
  </script>
</head>
<body>
  <div class="container">
    <h1>Consulta de Oficinas</h1>
    <p>Selecione sua área de atuação para ver as oficinas disponíveis.</p>
    <label for="area_atuacao">Área de Atuação</label>
    <select id="area_atuacao" onchange="loadOficinasConsulta()">
      <option value="">Selecione...</option>
      <?php foreach($areas as $area): ?>
        <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
      <?php endforeach; ?>
    </select>
    <div id="oficinasContainer">
      <!-- As oficinas serão carregadas aqui via AJAX -->
    </div>
    <div class="totals">
      Total de Inscrições: <?php echo $totalRegistrations; ?> | 
      Total de Vagas: <?php echo $totalVagasTotais; ?> | 
      Vagas Preenchidas: <?php echo $totalRegistrations; ?> | 
      Vagas Restantes: <?php echo $totalVagasRestantes; ?>
    </div>
  </div>
</body>
</html>
