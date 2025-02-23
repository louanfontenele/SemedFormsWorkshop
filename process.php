<?php
require 'db.php';
$config = include 'config.php';

// Verifica se ainda está no período de inscrições
$currentDate = date("Y-m-d H:i:s");
if ($currentDate < $config['registration_start']) {
    die("As inscrições ainda não começaram.");
}
if ($currentDate > $config['registration_end']) {
    die("As inscrições foram encerradas.");
}

// Recebe os dados do formulário (CPF com pontuação)
$nome         = mb_convert_case(trim($_POST['nome'] ?? ''), MB_CASE_TITLE, "UTF-8");
$cpf          = trim($_POST['cpf'] ?? '');
$email        = strtolower(trim($_POST['email'] ?? ''));
$telefone     = trim($_POST['telefone'] ?? '');
$escola       = mb_convert_case(trim($_POST['escola'] ?? ''), MB_CASE_TITLE, "UTF-8");
$formacao     = mb_convert_case(trim($_POST['formacao'] ?? ''), MB_CASE_TITLE, "UTF-8");
$area_atuacao = trim($_POST['area_atuacao'] ?? '');
$oficina_id   = trim($_POST['oficina'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email inválido!");
}

// Verifica se CPF já foi cadastrado
$stmt = $db->prepare("SELECT * FROM registrations WHERE cpf = :cpf");
$stmt->execute([':cpf' => $cpf]);
if($stmt->fetch()) {
    die("Este CPF já foi cadastrado!");
}

$db->beginTransaction();

// Pega dados da oficina
$stmt = $db->prepare("SELECT descricao, vagas, escola as oficina_escola, endereco as oficina_endereco
                      FROM oficinas
                      WHERE id = :id");
$stmt->execute([':id' => $oficina_id]);
$office = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$office || $office['vagas'] <= 0) {
    $db->rollBack();
    die("Vagas esgotadas ou oficina inexistente.");
}

// Decrementa vaga
$stmt = $db->prepare("UPDATE oficinas SET vagas = vagas - 1 WHERE id = :id AND vagas > 0");
if(!$stmt->execute([':id' => $oficina_id])) {
    $db->rollBack();
    die("Erro ao atualizar vagas.");
}

// Salva no banco
$stmt = $db->prepare("INSERT INTO registrations
    (nome, cpf, email, telefone, escola, formacao, area_atuacao, oficina)
    VALUES
    (:nome, :cpf, :email, :telefone, :escola, :formacao, :area_atuacao, :oficina)");
$res = $stmt->execute([
    ':nome'         => $nome,
    ':cpf'          => $cpf,
    ':email'        => $email,
    ':telefone'     => $telefone,
    ':escola'       => $escola,
    ':formacao'     => $formacao,
    ':area_atuacao' => $area_atuacao,
    ':oficina'      => $oficina_id
]);

if($res) {
    $db->commit();
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
      <meta charset="UTF-8">
      <title>Cadastro Realizado</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          background: #f4f4f4;
          margin:0; 
          padding:0;
        }
        .container {
          max-width: 800px;
          margin: 20px auto;
          background: #fff;
          padding: 20px;
          box-shadow: 0 0 10px rgba(0,0,0,0.1);
          text-align: center;
        }
        ul {
          list-style: none;
          padding: 0;
          text-align: left;
          max-width: 600px;
          margin: 0 auto;
        }
        .btn {
          background: #28a745;
          color: #fff;
          border: none;
          padding: 10px 20px;
          border-radius: 5px;
          font-size: 16px;
          cursor: pointer;
          margin: 10px;
        }
        .btn:hover {
          background: #218838;
        }
      </style>
    </head>
    <body>
      <div class="container">
        <h2>Cadastro Realizado com Sucesso!</h2>
        <p>A seguir, os dados da sua inscrição:</p>
        <ul>
          <li><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></li>
          <li><strong>CPF:</strong> <?php echo htmlspecialchars($cpf); ?></li>
          <li><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></li>
          <li><strong>Telefone:</strong> <?php echo htmlspecialchars($telefone); ?></li>
          <li><strong>Escola de Atuação:</strong> <?php echo htmlspecialchars($escola); ?></li>
          <li><strong>Formação:</strong> <?php echo htmlspecialchars($formacao); ?></li>
          <li><strong>Área de Atuação:</strong> <?php echo htmlspecialchars($area_atuacao); ?></li>
          <li><strong>Oficina:</strong> <?php echo htmlspecialchars($office['descricao']); ?></li>
          <li><strong>Escola da Oficina:</strong> <?php echo htmlspecialchars($office['oficina_escola']); ?></li>
          <li><strong>Endereço da Oficina:</strong> <?php echo htmlspecialchars($office['oficina_endereco']); ?></li>
        </ul>
        <?php
          // Se existir um endereço, incorporamos Google Maps + botão para abrir no Maps
          if(!empty($office['oficina_endereco'])) {
              $encodedAddr = urlencode($office['oficina_endereco']);
              ?>
              <h3>Localização no Google Maps</h3>
              <iframe
                width="600"
                height="450"
                style="border:0;"
                src="https://www.google.com/maps?q=<?php echo $encodedAddr; ?>&output=embed"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
              </iframe>
              <br>
              <button class="btn" onclick="window.open('https://www.google.com/maps?q=<?php echo $encodedAddr; ?>', '_blank')">Abrir no Google Maps</button>
              <?php
          }
        ?>
        <br>
        <button class="btn" onclick="window.print()">Imprimir</button>
        <button class="btn" onclick="window.location.href='index.php'">Nova Inscrição</button>
      </div>
    </body>
    </html>
    <?php
} else {
    $db->rollBack();
    echo "Erro ao realizar o cadastro.";
}
?>
