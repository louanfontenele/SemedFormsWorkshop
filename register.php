<?php
// register.php
$config = include 'config.php';
date_default_timezone_set('America/Sao_Paulo');

$startDate = strtotime($config['registration_start']);
$endDate   = strtotime($config['registration_end']);
$currentDate = time();

// Se não estiver no período de inscrição, aborta
if ($currentDate < $startDate) {
    die("As inscrições ainda não começaram. Volte em " . date("d/m/Y H:i", $startDate) . ".");
}
if ($currentDate > $endDate) {
    die("As inscrições foram encerradas em " . date("d/m/Y H:i", $endDate) . ".");
}

// Carrega listas
$escolas = include 'escolas.php';
sort($escolas, SORT_STRING | SORT_FLAG_CASE);
$areas   = include 'areas.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Inscrição - <?php echo nl2br(htmlspecialchars($config['event_name'])); ?></title>
  <!-- Awesomplete CSS/JS -->
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
    h2, h1 { color: #333; }
    label { display: block; margin: 10px 0 5px; }
    .required::after { content: " *"; color: red; }
    input, select, button {
      width: 100%; 
      padding: 10px; 
      margin-bottom: 5px;
      border: 1px solid #ccc; 
      border-radius: 5px; 
      box-sizing: border-box;
    }
    .small-text { 
      font-size: 12px; 
      color: #666; 
      margin-bottom: 10px; 
    }
    .buttons { 
      display: flex; 
      justify-content: space-between; 
      margin-top: 10px; 
    }
    .step { display: none; }
    .step.active { display: block; }
    .oficina-option {
      margin: 10px 0; 
      padding: 10px; 
      border: 1px solid #ddd; 
      border-radius: 5px;
    }
    .oficina-option input { margin-right: 10px; }
    .contact-wrapper { text-align: center; margin: 20px; }
    .contact-btn {
      background: #007bff; 
      color: #fff; 
      padding: 8px 12px;
      border: none; 
      border-radius: 5px; 
      cursor: pointer;
    }
    .contact-btn:hover { background: #0056b3; }
    #reviewTable {
      width: 100%; 
      border-collapse: collapse; 
      margin-top: 15px;
    }
    #reviewTable th, #reviewTable td {
      border: 1px solid #ccc; 
      padding: 8px; 
      text-align: left;
    }
    #reviewTable th { background-color: #007bff; color: #fff; }
    .awesomplete {
      display: block; 
      width: 100%; 
      box-sizing: border-box; 
      padding: 10px;
    }
    .awesomplete ul { 
      max-height: 150px; 
      overflow-y: auto; 
    }
  </style>
</head>
<body>
<div class="container" id="formContainer">
  <?php if(!empty($config['banner_url'])): ?>
    <img src="<?php echo htmlspecialchars($config['banner_url']); ?>" alt="Banner" style="max-width: 100%; height: auto; margin-bottom: 20px;">
  <?php endif; ?>

  <form id="registrationForm" action="process.php" method="POST">
    <!-- Etapa 1: Dados Pessoais -->
    <div class="step active" id="step1">
      <h2>Dados Pessoais</h2>
      <label for="nome" class="required">Nome Completo</label>
      <input type="text" id="nome" name="nome" required placeholder="Digite seu nome completo">
      <div class="small-text">Ex.: João da Silva</div>
      
      <label for="cpf" class="required">CPF</label>
      <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00"
             oninput="this.value = formatCPF(this.value)"
             onblur="checkCPFExists()">
      <div class="small-text">Digite apenas dígitos; a formatação será adicionada automaticamente.</div>
      
      <label for="email" class="required">Email</label>
      <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
      <div class="small-text">Informe um email válido que você tenha acesso.</div>
      
      <label for="telefone" class="required">Telefone</label>
      <input type="text" id="telefone" name="telefone" required placeholder="(99) 9 9999-9999"
             oninput="this.value = formatPhone(this.value)">
      <div class="small-text">Digite apenas dígitos; a formatação será adicionada automaticamente.</div>
      
      <label for="escola" class="required">Escola de Atuação</label>
      <input class="awesomplete" data-minchars="0" data-autofirst="true"
             id="escola" name="escola" placeholder="Selecione a escola" required>
      <div class="small-text">Selecione a escola onde você atua.</div>
      
      <div class="buttons">
        <button type="button" onclick="window.location.href='index.php'">Voltar</button>
        <button type="button" onclick="nextStep()">Próximo</button>
      </div>
    </div>
    
    <!-- Etapa 2: Área de Atuação -->
    <div class="step" id="step2">
      <h2>Área de Atuação</h2>
      <label for="area_atuacao" class="required">Área de Atuação</label>
      <select id="area_atuacao" name="area_atuacao" required>
        <option value="">Selecione...</option>
        <?php foreach($areas as $area): ?>
          <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
        <?php endforeach; ?>
      </select>
      <div class="small-text">Selecione sua área de atuação.</div>
      <div class="buttons">
        <button type="button" onclick="prevStep()">Voltar</button>
        <button type="button" onclick="nextStep()">Próximo</button>
      </div>
    </div>
    
    <!-- Etapa 3: Seleção de Oficina -->
    <div class="step" id="step3">
      <h2>Selecione uma Oficina</h2>
      <p>Escolha uma oficina disponível para sua área de atuação (apenas uma oficina pode ser selecionada).<br>
         Se a oficina estiver <strong>desabilitada</strong>, as vagas se esgotaram.</p>
      <div id="oficinasContainer"></div>
      <div class="buttons">
        <button type="button" onclick="prevStep()">Voltar</button>
        <button type="button" onclick="nextStep()">Próximo</button>
      </div>
    </div>
    
    <!-- Etapa 4: Revisão e Confirmação -->
    <div class="step" id="step4">
      <h2>Revisão e Confirmação</h2>
      <p><em>Verifique todos os dados. Após a confirmação, não será possível alterá-los!</em></p>
      <div id="review"></div>
      <div class="buttons">
        <button type="button" onclick="prevStep()">Voltar para Corrigir</button>
        <button type="submit">Finalizar e Confirmar Inscrição</button>
      </div>
    </div>
  </form>
</div>

<div class="contact-wrapper">
  <button class="contact-btn" onclick="window.location.href='contact.php'">Contato</button>
</div>

<?php include 'footer.php'; ?>

<script>
  // Lista de escolas para Awesomplete
  const escolasJSOriginal = <?php echo json_encode($escolas); ?>;
  let currentStep = 0;
  let cpfExists = false;
  const steps = document.querySelectorAll('.step');

  // Função para normalizar strings (removendo acentos e espaços extras)
  function normalize(str) {
    return str.normalize("NFD")
              .replace(/\p{Diacritic}/gu, "")
              .replace(/\s+/g, " ")
              .trim()
              .toLowerCase();
  }
  const escolasJS = escolasJSOriginal.map(e => normalize(e));

  function validateCurrentStep() {
    const requiredFields = steps[currentStep].querySelectorAll('[required]');
    for (let field of requiredFields) {
      if(!field.value.trim()) {
        alert("Preencha o campo: " + (field.placeholder || field.name));
        return false;
      }
      if(currentStep === 0 && field.id === 'cpf') {
        if(!validateCPF(field.value)) {
          alert("CPF inválido!");
          return false;
        }
        if(cpfExists) {
          alert("Este CPF já foi cadastrado! Corrija o CPF.");
          return false;
        }
      }
    }
    return true;
  }

  function nextStep() {
    if(!validateCurrentStep()) return;

    if(currentStep === 0) {
      let cpfVal = document.getElementById('cpf').value;
      if(!validateCPF(cpfVal)) {
        alert("CPF inválido!");
        return;
      }
      if(cpfExists) {
        alert("Este CPF já foi cadastrado! Corrija o CPF.");
        return;
      }
      let escolaVal = normalize(document.getElementById('escola').value);
      if(!escolasJS.includes(escolaVal)) {
        alert("Selecione uma escola válida da lista!");
        return;
      }
      // Ajusta capitalização
      document.getElementById('nome').value = toTitleCase(document.getElementById('nome').value);
      document.getElementById('escola').value = toTitleCase(document.getElementById('escola').value);
      document.getElementById('email').value = document.getElementById('email').value.toLowerCase();
    }

    if(currentStep === 1) {
      loadOficinas();
    }

    if(currentStep === 2) {
      const radio = document.querySelector('input[name="oficina"]:checked');
      if(!radio) {
        alert("Selecione uma oficina! Se estiver desabilitada, as vagas acabaram.");
        return;
      }
      populateReview();
    }

    if(currentStep < steps.length - 1) {
      currentStep++;
      showStep(currentStep);
    }
  }

  function prevStep() {
    if(currentStep > 0) {
      currentStep--;
      showStep(currentStep);
    } else {
      window.location.href = 'index.php';
    }
  }

  function showStep(n) {
    steps.forEach((step, i) => {
      step.classList.toggle('active', i === n);
    });
  }

  function loadOficinas() {
    const area = document.getElementById('area_atuacao').value;
    const container = document.getElementById('oficinasContainer');
    container.innerHTML = 'Carregando oficinas...';
    fetch('get_oficinas.php?area=' + encodeURIComponent(area))
      .then(r => r.json())
      .then(data => {
        if(data.length === 0) {
          container.innerHTML = '<p>Nenhuma oficina disponível para sua área.</p>';
          return;
        }
        let html = '';
        data.forEach(of => {
          html += `
            <div class="oficina-option">
              <input type="radio" name="oficina" id="oficina_${of.id}" value="${of.id}"
                     data-desc="${of.descricao}"
                     data-escola="${of.escola || ''}"
                     data-endereco="${of.endereco || ''}"
                     ${of.vagas <= 0 ? 'disabled' : ''} required>
              <label for="oficina_${of.id}">${of.descricao} - ${of.vagas} Vagas - ${of.horas}</label>
            </div>
          `;
        });
        container.innerHTML = html;
      })
      .catch(err => {
        console.error(err);
        container.innerHTML = `<p>Erro ao carregar oficinas: ${err}</p>`;
      });
  }

  // Como optamos por não utilizar atualização em tempo real constante, as vagas serão atualizadas quando o usuário avançar para a etapa 3.
  function populateReview() {
    const nome = document.getElementById('nome').value;
    const cpf = document.getElementById('cpf').value;
    const email = document.getElementById('email').value;
    const telefone = document.getElementById('telefone').value;
    const escola = document.getElementById('escola').value;
    const area = document.getElementById('area_atuacao').value;
    const radio = document.querySelector('input[name="oficina"]:checked');
    const desc = radio ? radio.dataset.desc : '';
    const escOf = radio ? radio.dataset.escola : '';
    const endOf = radio ? radio.dataset.endereco : '';
    let html = `
      <table id="reviewTable">
        <tr><th>Campo</th><th>Valor</th></tr>
        <tr><td>Nome</td><td>${nome}</td></tr>
        <tr><td>CPF</td><td>${cpf}</td></tr>
        <tr><td>Email</td><td>${email}</td></tr>
        <tr><td>Telefone</td><td>${telefone}</td></tr>
        <tr><td>Escola de Atuação</td><td>${escola}</td></tr>
        <tr><td>Área de Atuação</td><td>${area}</td></tr>
        <tr><td>Oficina</td><td>${desc}</td></tr>
        <tr><td>Escola da Oficina</td><td>${escOf}</td></tr>
        <tr><td>Endereço da Oficina</td><td>${endOf}</td></tr>
      </table>
    `;
    document.getElementById('review').innerHTML = html;
  }

  function checkCPFExists() {
    let cpf = document.getElementById('cpf').value;
    if(cpf.length !== 14) return;
    fetch('check_cpf.php?cpf=' + encodeURIComponent(cpf))
      .then(response => response.json())
      .then(data => {
        if(data.exists) {
          alert("Este CPF já foi cadastrado!");
          cpfExists = true;
          document.getElementById('cpf').focus();
        } else {
          cpfExists = false;
        }
      })
      .catch(err => {
        console.error(err);
      });
  }

  function validateCPF(cpfStr) {
    let cpf = cpfStr.replace(/\D/g, '');
    if(cpf.length !== 11) return false;
    if(/^(\d)\1+$/.test(cpf)) return false;
    let add = 0;
    for(let i = 0; i < 9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
    let rev = 11 - (add % 11);
    if(rev === 10 || rev === 11) rev = 0;
    if(rev !== parseInt(cpf.charAt(9))) return false;
    add = 0;
    for(let i = 0; i < 10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if(rev === 10 || rev === 11) rev = 0;
    if(rev !== parseInt(cpf.charAt(10))) return false;
    return true;
  }

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
      return "(" + v.slice(0, 2) + ") " + v.slice(2);
    } else if(v.length >= 4 && v.length < 8) {
      return "(" + v.slice(0, 2) + ") " + v.slice(2, 3) + " " + v.slice(3);
    } else if(v.length >= 8) {
      return "(" + v.slice(0, 2) + ") " + v.slice(2, 3) + " " + v.slice(3, 7) + "-" + v.slice(7);
    }
    return v;
  }

  function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt) {
      return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
  }

  window.addEventListener('load', () => {
    // Awesomplete para o campo de escola
    let awEscola = new Awesomplete(document.getElementById('escola'), {
      list: <?php echo json_encode($escolas); ?>,
      minChars: 0,
      autoFirst: true,
      maxItems: 200
    });
    document.getElementById('escola').addEventListener('focus', function(){
      awEscola.evaluate();
    });
  });
</script>
</body>
</html>
