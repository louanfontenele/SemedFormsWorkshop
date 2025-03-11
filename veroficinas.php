<?php
// oficinas.html (ou oficinas.php)
$config = include 'config.php';
$areas   = include 'areas.php';
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
      border: 1px solid #ddd;
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
  </style>
  <script>
    function loadOficinasConsulta() {
      var area = document.getElementById('area_atuacao').value;
      var container = document.getElementById('oficinasContainer');
      container.innerHTML = 'Carregando oficinas...';
      fetch('get_oficinas.php?area=' + encodeURIComponent(area))
        .then(response => response.json())
        .then(data => {
          if (!data || data.length === 0) {
            container.innerHTML = '<p>Nenhuma oficina encontrada para esta área.</p>';
            return;
          }
          let html = '';
          data.forEach(function(of) {
            // Exibe apenas a descrição, horário, escola e endereço (sem as vagas)
            html += '<div class="oficina-item">';
            html += '<strong>' + of.descricao + '</strong><br>';
            html += 'Horário: ' + of.horas + '<br>';
            if (of.escola) {
              html += 'Escola: ' + of.escola + '<br>';
            }
            if (of.endereco) {
              html += 'Endereço: ' + of.endereco + '<br>';
            }
            html += '</div>';
          });
          container.innerHTML = html;
        })
        .catch(error => {
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
    <a class="btn" href="index.php">Voltar ao Início</a>
  </div>
</body>
</html>
