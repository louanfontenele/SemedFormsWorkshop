<?php
// print_clean.php
require 'db.php';
$config = include 'config.php';

$cpf = isset($_GET['cpf']) ? trim($_GET['cpf']) : '';

if(!$cpf) {
    die("CPF não informado.");
}

$cpfNumeric = preg_replace('/\D/', '', $cpf);
if(strlen($cpfNumeric) < 11) {
    die("CPF inválido.");
}

$stmt = $db->prepare("SELECT r.nome, r.cpf, r.email, r.telefone, r.escola, r.area_atuacao,
                             o.descricao as oficina_desc, o.escola as oficina_escola, o.endereco as oficina_endereco
                      FROM registrations r
                      LEFT JOIN oficinas o ON r.oficina = o.id
                      WHERE REPLACE(REPLACE(REPLACE(r.cpf, '.', ''), '-', ''), ' ', '') = :cpfNumeric");
$stmt->execute([':cpfNumeric' => $cpfNumeric]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$registration) {
    die("Inscrição não encontrada.");
}

function safe($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title><?php echo safe($config['event_name']); ?> - Impressão</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      margin: 20px;
    }
    .container {
      width: 100%;
      max-width: 800px;
      margin: auto;
    }
    h2, h3 {
      text-align: center;
      margin-top: 0;
    }
    .banner {
      max-width: 100%;
      height: auto;
      margin-bottom: 20px;
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
      text-align: left;
    }
    th {
      background: #007bff;
      color: #fff;
    }
    .contact, .map, .signature {
      margin-top: 20px;
    }
    .map {
      text-align: center;
    }
    .signature {
      margin-top: 20px;
      text-align: center;
      page-break-inside: avoid;
    }
    .signature-line {
      border-top: 1px solid #000;
      width: 300px;
      margin: 10px auto;
    }
    .signature-text {
      font-weight: bold;
      text-align: center;
      margin-top: 5px;
    }
    .signature-instructions {
      font-size: 10px;
      font-style: italic;
      color: #333;
      margin-top: 5px;
    }
    @media print {
      .no-print { display: none; }
    }
  </style>
</head>
<body>
  <div class="container">
    <?php if(!empty($config['banner_url'])): ?>
      <center><img src="<?php echo safe($config['banner_url']); ?>" alt="Banner" class="banner" width="30%"></center>
    <?php endif; ?>

    <h2>Inscrição Confirmada</h2>
    <h3><?php echo nl2br(string: safe ($config['event_name'])) ; ?></h3>

    <table>
      <tr><th>Campo</th><th>Valor</th></tr>
      <tr><td>Nome</td><td><?php echo safe($registration['nome']); ?></td></tr>
      <tr><td>CPF</td><td><?php echo safe($registration['cpf']); ?></td></tr>
      <tr><td>Email</td><td><?php echo safe($registration['email']); ?></td></tr>
      <tr><td>Telefone</td><td><?php echo safe($registration['telefone']); ?></td></tr>
      <tr><td>Escola de Atuação</td><td><?php echo safe($registration['escola']); ?></td></tr>
      <tr><td>Área de Atuação</td><td><?php echo safe($registration['area_atuacao']); ?></td></tr>
      <tr><td>Oficina</td><td><?php echo safe($registration['oficina_desc']); ?></td></tr>
      <tr><td>Escola da Oficina</td><td><?php echo safe($registration['oficina_escola']); ?></td></tr>
      <tr><td>Endereço da Oficina</td><td><?php echo safe($registration['oficina_endereco']); ?></td></tr>
    </table>

    <?php if(!empty($registration['oficina_endereco'])): ?>
      <div class="map">
        <iframe
          width="100%"
          height="150"
          style="border:1px solid #ccc;"
          src="https://www.google.com/maps?q=<?php echo urlencode($registration['oficina_endereco']); ?>&output=embed"
          allowfullscreen
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    <?php endif; ?>

    <div class="contact">
      <p><strong>Informações de Contato:</strong><br>
      <?php echo nl2br(safe($config['contact_info'])); ?></p>
      <p><strong>Local de Abertura:</strong><br>
      <?php echo safe($config['opening_address']); ?></p>
    </div>
<br/>
    <div class="signature">
      <div class="signature-line"></div>
      <div class="signature-text">Assinatura</div>
      <div class="signature-instructions">
        Por favor, escreva o nome completo, sem rubricar e sem abreviar.
      </div>
    </div>

    <div class="no-print" style="text-align:center; margin-top:20px;">
      <button onclick="window.print()">Imprimir</button>
      <button onclick="window.location.href='index.php'">Voltar</button>
    </div>
  </div>
</body>
</html>
