<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

require 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    die("ID inválido.");
}

// Carrega a inscrição
$stmt = $db->prepare("SELECT * FROM registrations WHERE id = :id");
$stmt->execute([':id' => $id]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$registration) {
    die("Inscrição não encontrada.");
}

// Carrega lista de escolas
$escolas = include 'escolas.php';
sort($escolas, SORT_STRING | SORT_FLAG_CASE);

// Carrega lista de áreas
$areas = include 'areas.php';

// Carrega a oficina atual (para sabermos se trocou)
$old_oficina_id = $registration['oficina'];

// Se o formulário foi enviado (botão "Atualizar Inscrição")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = mb_convert_case(trim($_POST['nome'] ?? ''), MB_CASE_TITLE, "UTF-8");
    $cpf             = trim($_POST['cpf'] ?? '');
    $email           = strtolower(trim($_POST['email'] ?? ''));
    $telefone        = trim($_POST['telefone'] ?? '');
    $escola          = mb_convert_case(trim($_POST['escola'] ?? ''), MB_CASE_TITLE, "UTF-8");
    $area_atuacao    = trim($_POST['area_atuacao'] ?? '');
    $nova_oficina_id = trim($_POST['oficina'] ?? '');

    try {
        $db->beginTransaction();

        // Se mudou de oficina, incrementa a vaga na antiga e decrementa na nova
        if ($old_oficina_id && $old_oficina_id != $nova_oficina_id) {
            // Incrementa a antiga
            $stmtInc = $db->prepare("UPDATE oficinas SET vagas = vagas + 1 WHERE id = :oldId");
            $stmtInc->execute([':oldId' => $old_oficina_id]);

            // Decrementa a nova (se houver nova)
            if (!empty($nova_oficina_id)) {
                $stmtDec = $db->prepare("UPDATE oficinas SET vagas = vagas - 1 
                                         WHERE id = :newId AND vagas > 0");
                $okDec = $stmtDec->execute([':newId' => $nova_oficina_id]);
                if (!$okDec) {
                    $db->rollBack();
                    die("Erro ao atualizar vagas da nova oficina.");
                }
            }
        }

        // Atualiza a inscrição no banco (sem 'formacao')
        $stmtUp = $db->prepare("
            UPDATE registrations
               SET nome         = :nome,
                   cpf          = :cpf,
                   email        = :email,
                   telefone     = :telefone,
                   escola       = :escola,
                   area_atuacao = :area_atuacao,
                   oficina      = :oficina
             WHERE id           = :id
        ");
        $okUp = $stmtUp->execute([
            ':nome'         => $nome,
            ':cpf'          => $cpf,
            ':email'        => $email,
            ':telefone'     => $telefone,
            ':escola'       => $escola,
            ':area_atuacao' => $area_atuacao,
            ':oficina'      => $nova_oficina_id,
            ':id'           => $id
        ]);

        if (!$okUp) {
            $db->rollBack();
            die("Erro ao atualizar inscrição.");
        }

        // (Opcional) Atualiza snapshot de vagas (vagas.json)
        $stmtSnap = $db->query("SELECT id, vagas FROM oficinas");
        $vagasSnapshot = $stmtSnap->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents('vagas.json', json_encode($vagasSnapshot));

        $db->commit();

        // Redireciona de volta ao dashboard
        header("Location: admin.php");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        die("Erro ao atualizar inscrição: " . $e->getMessage());
    }
}

// Carrega as oficinas (iremos exibir via AJAX no front)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Editar Inscrição</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Awesomplete para escola -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/awesomplete@1.1.5/awesomplete.css">
  <script src="https://cdn.jsdelivr.net/npm/awesomplete@1.1.5/awesomplete.min.js"></script>
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
    }
    input, select, button {
      width: 100%;
      padding: 10px;
      margin: 5px 0;
      box-sizing: border-box;
    }
    label {
      margin-top: 10px;
      display: block;
    }
    .buttons {
      margin-top: 10px;
      display: flex;
      justify-content: space-between;
    }
    #oficinasContainer .oficina-option {
      border: 1px solid #ddd;
      padding: 8px;
      border-radius: 5px;
      margin-bottom: 5px;
    }
    #oficinasContainer input[type="radio"] {
      margin-right: 8px;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Editar Inscrição (ID: <?php echo htmlspecialchars($registration['id']); ?>)</h2>

  <form method="POST" action="?id=<?php echo $registration['id']; ?>">

    <label for="nome">Nome Completo:</label>
    <input type="text" id="nome" name="nome"
           value="<?php echo htmlspecialchars($registration['nome']); ?>"
           required>

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
    <input class="awesomplete"
           id="escola"
           name="escola"
           value="<?php echo htmlspecialchars($registration['escola']); ?>"
           required
           data-minchars="0"
           data-autofirst="true">

    <label for="area_atuacao">Área de Atuação:</label>
    <select id="area_atuacao" name="area_atuacao" required onchange="loadOficinasAdmin()">
      <option value="">Selecione...</option>
      <?php foreach ($areas as $area): ?>
        <option value="<?php echo htmlspecialchars($area); ?>"
          <?php if ($registration['area_atuacao'] == $area) echo 'selected'; ?>>
          <?php echo htmlspecialchars($area); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <br>
    <h4>Oficina</h4>
    <div id="oficinasContainer">
      <!-- Aqui exibiremos as oficinas (rádio) via AJAX -->
    </div>

    <div class="buttons">
      <button type="submit">Atualizar Inscrição</button>
      <a href="admin.php">Voltar</a>
    </div>
  </form>
</div>

<script>
/***************************************************
 * Formatação de CPF e Telefone
 ***************************************************/
function formatCPF(value) {
  let v = value.replace(/\D/g, '');
  if (v.length > 11) v = v.slice(0, 11);
  if (v.length >= 4 && v.length < 7) {
    return v.replace(/(\d{3})(\d+)/, "$1.$2");
  } else if (v.length >= 7 && v.length < 11) {
    return v.replace(/(\d{3})(\d{3})(\d+)/, "$1.$2.$3");
  } else if (v.length === 11) {
    return v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
  }
  return v;
}

function formatPhone(value) {
  let v = value.replace(/\D/g, '');
  if (v.length > 11) v = v.slice(0, 11);
  if (v.length >= 1 && v.length < 3) {
    return "(" + v;
  } else if (v.length >= 3 && v.length < 4) {
    return "(" + v.slice(0,2) + ") " + v.slice(2);
  } else if (v.length >= 4 && v.length < 8) {
    return "(" + v.slice(0,2) + ") " + v.slice(2,3) + " " + v.slice(3);
  } else if (v.length >= 8) {
    return "(" + v.slice(0,2) + ") " + v.slice(2,3) + " " + v.slice(3,7) + "-" + v.slice(7);
  }
  return v;
}

/***************************************************
 * Awesomplete para Escola
 ***************************************************/
const escolasJS = <?php echo json_encode($escolas); ?>;

window.addEventListener('load', () => {
  // Awesomplete para escola
  let awEscola = new Awesomplete(document.getElementById('escola'), {
    list: escolasJS,
    minChars: 0,
    autoFirst: true,
    maxItems: 200
  });
  document.getElementById('escola').addEventListener('focus', function() {
    awEscola.evaluate();
  });

  // Carrega oficinas correspondentes à área atual
  loadOficinasAdmin();
});

/***************************************************
 * Carrega oficinas via get_oficinas.php e exibe 
 * como lista de inputs type="radio"
 ***************************************************/
function loadOficinasAdmin() {
  const areaVal = document.getElementById('area_atuacao').value;
  const container = document.getElementById('oficinasContainer');
  container.innerHTML = '';

  if (!areaVal) {
    container.innerHTML = '<p>Selecione uma área de atuação para ver as oficinas.</p>';
    return;
  }

  container.innerHTML = '<p>Carregando oficinas...</p>';
  fetch('get_oficinas.php?area=' + encodeURIComponent(areaVal))
    .then(r => r.json())
    .then(data => {
      if (!data || data.length === 0) {
        container.innerHTML = '<p>Nenhuma oficina disponível para essa área.</p>';
        return;
      }

      // Pega a oficina atual do registro
      const currentOficinaId = '<?php echo $registration['oficina']; ?>';
      let html = '';

      data.forEach(of => {
        const descCompleta = of.descricao + ' - ' + of.vagas + ' vagas - ' + of.horas;
        const checked  = (of.id == currentOficinaId) ? 'checked' : '';
        const disabled = (of.vagas <= 0) ? 'disabled' : '';

        html += `
          <div class="oficina-option">
            <input type="radio" name="oficina" id="of_${of.id}" value="${of.id}"
                   ${checked} ${disabled} required>
            <label for="of_${of.id}" title="${descCompleta}">
              ${descCompleta}
            </label>
          </div>
        `;
      });
      container.innerHTML = html;
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = '<p style="color:red;">Erro ao carregar oficinas.</p>';
    });
}
</script>
</body>
</html>
