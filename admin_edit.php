<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
require 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$id) {
    die("ID inválido.");
}

// Carrega a inscrição
$stmt = $db->prepare("SELECT * FROM registrations WHERE id = :id");
$stmt->execute([':id' => $id]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$registration) {
    die("Inscrição não encontrada.");
}

// Carrega as listas para os dropdowns
$escolas = include 'escolas.php';
sort($escolas, SORT_STRING | SORT_FLAG_CASE);
$formacoes = include 'formacoes.php';
sort($formacoes, SORT_STRING | SORT_FLAG_CASE);
$areas = include 'areas.php';

// Carrega as oficinas (todas)
$stmt2 = $db->query("SELECT id, descricao, vagas FROM oficinas ORDER BY descricao ASC");
$oficinas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Processa o formulário
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mb_convert_case(trim($_POST['nome'] ?? ''), MB_CASE_TITLE, "UTF-8");
    $cpf = trim($_POST['cpf'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $telefone = trim($_POST['telefone'] ?? '');
    $escola = mb_convert_case(trim($_POST['escola'] ?? ''), MB_CASE_TITLE, "UTF-8");
    $formacao = mb_convert_case(trim($_POST['formacao'] ?? ''), MB_CASE_TITLE, "UTF-8");
    $area_atuacao = trim($_POST['area_atuacao'] ?? '');
    $nova_oficina_id = trim($_POST['oficina'] ?? '');

    $old_oficina_id = $registration['oficina'];
    
    $db->beginTransaction();

    if($old_oficina_id != $nova_oficina_id) {
        // Incrementa a vaga na oficina antiga
        if($old_oficina_id) {
            $stmt = $db->prepare("UPDATE oficinas SET vagas = vagas + 1 WHERE id = :id");
            $stmt->execute([':id' => $old_oficina_id]);
        }
        // Decrementa a vaga na nova oficina
        $stmt = $db->prepare("UPDATE oficinas SET vagas = vagas - 1 WHERE id = :id AND vagas > 0");
        if(!$stmt->execute([':id' => $nova_oficina_id])) {
            $db->rollBack();
            die("Erro ao atualizar vagas da nova oficina.");
        }
    }
    
    // Atualiza a inscrição
    $stmt = $db->prepare("UPDATE registrations
        SET nome = :nome,
            cpf = :cpf,
            email = :email,
            telefone = :telefone,
            escola = :escola,
            formacao = :formacao,
            area_atuacao = :area_atuacao,
            oficina = :oficina
        WHERE id = :id
    ");
    $res = $stmt->execute([
        ':nome' => $nome,
        ':cpf' => $cpf,
        ':email' => $email,
        ':telefone' => $telefone,
        ':escola' => $escola,
        ':formacao' => $formacao,
        ':area_atuacao' => $area_atuacao,
        ':oficina' => $nova_oficina_id,
        ':id' => $id
    ]);
    
    if($res) {
        $db->commit();
        header("Location: admin.php");
        exit;
    } else {
        $db->rollBack();
        die("Erro ao atualizar inscrição.");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Editar Inscrição</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Inclua Awesomplete para manter o mesmo layout -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/awesomplete@1.1.5/awesomplete.css">
  <script src="https://cdn.jsdelivr.net/npm/awesomplete@1.1.5/awesomplete.min.js"></script>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; }
    input, select, button { width: 100%; padding: 10px; margin: 5px 0; box-sizing: border-box; }
    label { margin-top: 10px; display: block; }
    .buttons { margin-top: 10px; display: flex; justify-content: space-between; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Editar Inscrição (ID: <?php echo htmlspecialchars($registration['id']); ?>)</h2>
    <form method="POST" action="admin_edit.php?id=<?php echo $registration['id']; ?>">
      <label for="nome">Nome Completo:</label>
      <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($registration['nome']); ?>" required>
      
      <label for="cpf">CPF:</label>
      <input type="text" id="cpf" name="cpf"
             value="<?php echo htmlspecialchars($registration['cpf']); ?>"
             required
             oninput="this.value = formatCPF(this.value)">
      
      <label for="email">Email:</label>
      <input type="email" id="email" name="email"
             value="<?php echo htmlspecialchars($registration['email']); ?>"
             required>
      
      <label for="telefone">Telefone:</label>
      <input type="text" id="telefone" name="telefone"
             value="<?php echo htmlspecialchars($registration['telefone']); ?>"
             required
             oninput="this.value = formatPhone(this.value)">
      
      <label for="escola">Escola de Atuação:</label>
      <!-- Awesomplete para escola -->
      <input class="awesomplete"
             id="escola"
             name="escola"
             value="<?php echo htmlspecialchars($registration['escola']); ?>"
             required
             data-minchars="0"
             data-autofirst="true">
      
      <label for="formacao">Formação:</label>
      <!-- Awesomplete para formação -->
      <input class="awesomplete"
             id="formacao"
             name="formacao"
             value="<?php echo htmlspecialchars($registration['formacao']); ?>"
             required
             data-minchars="0"
             data-autofirst="true">
      
      <label for="area_atuacao">Área de Atuação:</label>
      <select id="area_atuacao" name="area_atuacao" required>
        <?php foreach($areas as $area): ?>
          <option value="<?php echo htmlspecialchars($area); ?>"
                  <?php if($registration['area_atuacao'] == $area) echo 'selected'; ?>>
            <?php echo htmlspecialchars($area); ?>
          </option>
        <?php endforeach; ?>
      </select>
      
      <label for="oficina">Oficina:</label>
      <!-- Dropdown com as oficinas (nome e vagas) -->
      <select id="oficina" name="oficina" required>
        <?php foreach($oficinas as $of): ?>
          <option value="<?php echo $of['id']; ?>"
                  <?php if($registration['oficina'] == $of['id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($of['descricao']); ?> - <?php echo htmlspecialchars($of['vagas']); ?> vagas
          </option>
        <?php endforeach; ?>
      </select>
      
      <div class="buttons">
        <button type="submit">Atualizar Inscrição</button>
        <a href="admin.php">Voltar</a>
      </div>
    </form>
  </div>
  
  <script>
    // Carrega arrays de escolas/formações
    const escolasJS = <?php echo json_encode($escolas); ?>;
    const formacoesJS = <?php echo json_encode($formacoes); ?>;
    
    // Instancia Awesomplete para manter a experiência igual ao index.php
    window.addEventListener('load', () => {
      let awEscola = new Awesomplete(document.getElementById('escola'), {
        list: escolasJS,
        minChars: 0,
        autoFirst: true,
        maxItems: 200
      });
      document.getElementById('escola').addEventListener('focus', function(){
        awEscola.evaluate();
      });
      
      let awFormacao = new Awesomplete(document.getElementById('formacao'), {
        list: formacoesJS,
        minChars: 0,
        autoFirst: true,
        maxItems: 100
      });
      document.getElementById('formacao').addEventListener('focus', function(){
        awFormacao.evaluate();
      });
    });
    
    function formatCPF(value) {
      let v = value.replace(/\D/g, '');
      if(v.length > 11) v = v.slice(0, 11);
      if(v.length >= 4 && v.length < 7) {
        return v.replace(/(\d{3})(\d+)/, "$1.$2");
      } else if(v.length >= 7 && v.length < 11) {
        return v.replace(/(\d{3})(\d{3})(\d+)/, "$1.$2.$3");
      } else if(v.length === 11) {
        return v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
      }
      return v;
    }
    function formatPhone(value) {
      let v = value.replace(/\D/g, '');
      if(v.length > 11) v = v.slice(0, 11);
      if(v.length >= 1 && v.length < 3) {
        return "(" + v;
      } else if(v.length >= 3 && v.length < 4) {
        return "(" + v.slice(0,2) + ") " + v.slice(2);
      } else if(v.length >= 4 && v.length < 8) {
        return "(" + v.slice(0,2) + ") " + v.slice(2,3) + " " + v.slice(3);
      } else if(v.length >= 8) {
        return "(" + v.slice(0,2) + ") " + v.slice(2,3) + " " + v.slice(3,7) + "-" + v.slice(7);
      }
      return v;
    }
  </script>
</body>
</html>
