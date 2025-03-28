<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

require 'db.php';
$config = include 'config.php';

date_default_timezone_set('America/Sao_Paulo');
// Removemos a verificação de período para admin:
// $currentDate = time();
// $startDate   = strtotime($config['registration_start']);
// $endDate     = strtotime($config['registration_end']);

// Recebe os dados do formulário
$nome         = mb_convert_case(trim($_POST['nome'] ?? ''), MB_CASE_TITLE, "UTF-8");
$cpf          = trim($_POST['cpf'] ?? '');
$email        = strtolower(trim($_POST['email'] ?? ''));
$telefone     = trim($_POST['telefone'] ?? '');
$escola       = mb_convert_case(trim($_POST['escola'] ?? ''), MB_CASE_TITLE, "UTF-8");
$area_atuacao = trim($_POST['area_atuacao'] ?? '');
$oficina_id   = trim($_POST['oficina'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email inválido!");
}

// Verifica se o CPF já foi cadastrado (opcional – mantenha se necessário)
$stmt = $db->prepare("SELECT id FROM registrations WHERE cpf = :cpf");
$stmt->execute([':cpf' => $cpf]);
if ($stmt->fetch()) {
    die("Este CPF já foi cadastrado!");
}

// Remove a pontuação do CPF para uso na URL, se necessário
$cpfNumeric = preg_replace('/\D/', '', $cpf);

try {
    $db->beginTransaction();

    // Busca dados da oficina selecionada
    $stmt = $db->prepare("SELECT descricao, vagas, escola as oficina_escola, endereco as oficina_endereco
                          FROM oficinas
                          WHERE id = :id");
    $stmt->execute([':id' => $oficina_id]);
    $office = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$office || $office['vagas'] <= 0) {
        $db->rollBack();
        die("Vagas esgotadas ou oficina inexistente.");
    }

    // Decrementa 1 vaga na oficina
    $stmt = $db->prepare("UPDATE oficinas SET vagas = vagas - 1 WHERE id = :id AND vagas > 0");
    $okVagas = $stmt->execute([':id' => $oficina_id]);
    if (!$okVagas) {
        $db->rollBack();
        die("Erro ao atualizar vagas.");
    }

    // Insere o registro de inscrição
    $stmt = $db->prepare("INSERT INTO registrations
        (nome, cpf, email, telefone, escola, area_atuacao, oficina)
        VALUES
        (:nome, :cpf, :email, :telefone, :escola, :area_atuacao, :oficina)");
    $okInsert = $stmt->execute([
        ':nome'         => $nome,
        ':cpf'          => $cpf,
        ':email'        => $email,
        ':telefone'     => $telefone,
        ':escola'       => $escola,
        ':area_atuacao' => $area_atuacao,
        ':oficina'      => $oficina_id
    ]);
    if (!$okInsert) {
        $db->rollBack();
        die("Erro ao inserir dados de inscrição.");
    }

    // Commit da transação
    $db->commit();

    // Atualiza o arquivo de snapshot de vagas (vagas.json)
    $stmt = $db->query("SELECT id, vagas FROM oficinas");
    $vagasSnapshot = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('vagas.json', json_encode($vagasSnapshot));

} catch (Exception $e) {
    $db->rollBack();
    die("Erro ao processar inscrição: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Inscrição Confirmada</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
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
      text-decoration: none;
      display: inline-block;
    }
    .btn:hover {
      background: #218838;
    }
    h2 {
      margin-top: 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Cadastro Realizado com Sucesso!</h2>
    <p>Sua inscrição para a <strong><?php echo nl2br(htmlspecialchars($config['event_name'])); ?></strong> foi confirmada.</p>
    <div style="margin: 20px 0;">
      <a class="btn" href="print_clean.php?cpf=<?php echo urlencode($cpfNumeric); ?>">Imprimir</a>
      <a class="btn" href="admin_register.php">Voltar ao Formulário</a>
    </div>
  </div>
</body>
</html>
