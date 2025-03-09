<?php
// index.php
$config = include 'config.php';

// Converte datas para timestamps
$startDate = strtotime($config['registration_start']);
$endDate   = strtotime($config['registration_end']);
$currentDate = time();
$consultationLimit = strtotime("+1 month", $endDate);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($config['event_name']); ?></title>
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
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
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
    .error {
      color: red;
      font-weight: bold;
      margin-top: 20px;
    }
    img.banner {
      max-width: 100%;
      height: auto;
      margin-bottom: 20px;
    }
    .program {
      text-align: left;
      margin-top: 30px;
    }
    .program h2 {
      margin-bottom: 10px;
    }
    .program ol {
      margin-left: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table, th, td {
      border: 1px solid #000;
    }
    th, td {
      padding: 8px;
      text-align: center;
    }
    th {
      background: #007bff;
      color: #fff;
    }
    /* Estiliza a div do rodapé (se necessário) */
    .footer-button-container {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>
<div class="container">
  <?php if(!empty($config['banner_url'])): ?>
    <img src="<?php echo htmlspecialchars($config['banner_url']); ?>" alt="Banner" class="banner" width="70%">
  <?php endif; ?>

  <h1><?php echo nl2br(string: htmlspecialchars($config['event_name'])); ?></h1>
  <p><?php echo nl2br(htmlspecialchars($config['welcome_message'])); ?></p>
  <p><strong>Período de Inscrições:</strong><br>
    <?php echo date("d/m/Y H:i", $startDate); ?> até <?php echo date("d/m/Y H:i", $endDate); ?>
  </p>
  <p><strong>Contato:</strong><br>
    <?php echo nl2br(htmlspecialchars($config['contact_info'])); ?>
  </p>

  <div class="program">
    <h2>Instruções Importantes</h2>
    <ol>
      <li>As inscrições ocorrerão por CPF; cada participante só poderá se inscrever uma única vez;</li>
      <li>Realize sua inscrição na sua área de atuação (muito importante);</li>
      <li>No final da inscrição, salve seu comprovante, imprima uma cópia e leve para a escola onde ocorrerá a oficina;</li>
      <li>O comprovante de inscrição contém o local de participação das oficinas (nome da escola e endereço);</li>
      <li>Em caso de dúvidas, entre em contato pelos telefones disponíveis no rodapé do comprovante de inscrição.</li>
    </ol>

    <h2>Programação</h2>
    <table>
      <tr>
        <th>Evento</th>
        <th>Data</th>
        <th>Horário</th>
        <th>Local</th>
      </tr>
      <tr>
        <td>Conferência de Abertura</td>
        <td>19/03/2025</td>
        <td>18h</td>
        <td>Templo Central da Assembleia de Deus</td>
      </tr>
      <tr>
        <td>Credenciamento</td>
        <td>20/03/2025</td>
        <td>8h</td>
        <td>Escola da sua oficina</td>
      </tr>
      <tr>
        <td>Oficinas Pedagógicas (Dia 1)</td>
        <td>20/03/2025</td>
        <td>8h às 12h e 13h30 às 17h30</td>
        <td>Escolas Municipais</td>
      </tr>
      <tr>
        <td>Oficinas Pedagógicas (Dia 2)</td>
        <td>21/03/2025</td>
        <td>8h às 12h e 13h30 às 17h30</td>
        <td>Escolas Municipais</td>
      </tr>
    </table>
  </div>

  <?php if($currentDate < $startDate): ?>
    <div class="error">
      As inscrições ainda não iniciaram. Volte em <?php echo date("d/m/Y H:i", $startDate); ?>.
    </div>
  <?php elseif($currentDate >= $startDate && $currentDate <= $endDate): ?>
    <a href="register.php" class="btn">Iniciar Inscrição</a>
    <a href="consulta_page.php" class="btn">Consultar Inscrição</a>
  <?php elseif($currentDate > $endDate && $currentDate <= $consultationLimit): ?>
    <div class="error">
      As inscrições foram encerradas, mas você pode consultar sua inscrição até <?php echo date("d/m/Y H:i", $consultationLimit); ?>.
    </div>
    <a href="consulta_page.php" class="btn">Consultar Inscrição</a>
  <?php else: ?>
    <div class="error">O período de consulta expirou.</div>
  <?php endif; ?>

  <div class="footer-button-container">
    <button class="btn" onclick="window.location.href='contact.php'">Contato</button>
  </div>
</div>

<!-- Inclui o footer.php -->
<?php include 'footer.php'; ?>
</body>
</html>
