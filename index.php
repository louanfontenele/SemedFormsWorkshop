<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscrição - Oficinas SEMED</title>

  <!-- CSS e JS da Awesomplete via CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/awesomplete@1.1.5/awesomplete.css">
  <script src="https://cdn.jsdelivr.net/npm/awesomplete@1.1.5/awesomplete.min.js"></script>
  
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0; 
      padding: 0;
      background: #f4f4f4;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2, h1 {
      color: #333;
    }
    label {
      display: block;
      margin: 10px 0 5px;
    }
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
    .step {
      display: none;
    }
    .step.active {
      display: block;
    }
    .oficina-option {
      margin: 10px 0;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .oficina-option input {
      margin-right: 10px;
    }
    .contact-wrapper {
      text-align: center;
      margin: 20px;
    }
    .contact-btn {
      background: #007bff;
      color: #fff;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .contact-btn:hover {
      background: #0056b3;
    }
    /* Tabela de revisão */
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
    #reviewTable th {
      background-color: #007bff;
      color: #fff;
    }
    /* Awesomplete para 100% de largura e com scroll */
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
  <?php
    date_default_timezone_set('America/Sao_Paulo');
    $config = include 'config.php';

    // Carrega listas e ordena
    $escolas = include 'escolas.php';
    sort($escolas, SORT_STRING | SORT_FLAG_CASE);
    // Removemos formacoes, pois não serão mais usadas.
    $areas = include 'areas.php';

    // Verifica data de início/fim de inscrições
    $currentDate = date("Y-m-d H:i:s");
    $startDate   = $config['registration_start'];
    $endDate     = $config['registration_end'];

    if ($currentDate < $startDate) {
        echo '<div class="container" style="text-align:center;">
                <h1>Inscrições não iniciadas</h1>
                <p>As inscrições começarão em ' . date("d/m/Y H:i", strtotime($startDate)) . '.<br>
                Por favor, volte mais tarde.</p>
              </div>';
        echo '<div class="contact-wrapper">
                <button class="contact-btn" onclick="window.location.href=\'contact.php\'">Contato</button>
              </div>';
        include 'footer.php';
        exit;
    }
    if ($currentDate > $endDate) {
        echo '<div class="container" style="text-align:center;">
                <h1>Inscrições Encerradas</h1>
                <p>O período de inscrições terminou em ' . date("d/m/Y H:i", strtotime($endDate)) . '.<br>
                Não é mais possível enviar novas inscrições.</p>
              </div>';
        echo '<div class="contact-wrapper">
                <button class="contact-btn" onclick="window.location.href=\'contact.php\'">Contato</button>
              </div>';
        include 'footer.php';
        exit;
    }
  ?>
  
  <div class="container" id="formContainer">
    <form id="registrationForm" action="process.php" method="POST">
      <!-- step0: Boas-vindas -->
      <div class="step active" id="step0">
        <h1>Bem-vindo ao Sistema de Inscrição</h1>
        <p><?php echo nl2br(htmlspecialchars($config['welcome_message'])); ?></p>
        <p><strong>Período de Inscrições:</strong> 
          <?php echo date("d/m/Y H:i", strtotime($startDate)); ?> 
          até 
          <?php echo date("d/m/Y H:i", strtotime($endDate)); ?>
        </p>
        <p><strong>Contato:</strong><br>
          <?php echo nl2br(htmlspecialchars($config['contact_info'])); ?>
        </p>
        <div class="buttons">
          <button type="button" onclick="nextStep()">Iniciar Inscrição</button>
          <button type="button" onclick="startConsulta()">Consultar Inscrição</button>
        </div>
      </div>
      
      <!-- step1: Dados Pessoais -->
      <div class="step" id="step1">
        <h2>Dados Pessoais</h2>
        
        <label for="nome">Nome Completo:</label>
        <input type="text" id="nome" name="nome" required placeholder="Digite seu nome completo">
        <div class="small-text">Ex.: João da Silva</div>
        
        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00"
               oninput="this.value = formatCPF(this.value)"
               onblur="checkCPFExists()">
        <div class="small-text">Digite apenas dígitos; a formatação será adicionada automaticamente.</div>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
        <div class="small-text">Informe um email válido que você tenha acesso.</div>
        
        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" required placeholder="(99) 9 9999-9999" 
               oninput="this.value = formatPhone(this.value)">
        <div class="small-text">Digite apenas dígitos; a formatação será adicionada automaticamente.</div>
        
        <label for="escola">Escola de Atuação:</label>
        <input class="awesomplete"
               data-minchars="0"
               data-autofirst="true"
               id="escola"
               name="escola"
               placeholder="Selecione a escola"
               required>
        <div class="small-text">Selecione a escola onde você atua.</div>
        
        <!-- O campo "Formação" foi removido -->
        
        <div class="buttons">
          <button type="button" onclick="prevStep()">Voltar</button>
          <button type="button" onclick="nextStep()">Próximo</button>
        </div>
      </div>
      
      <!-- step2: Área de Atuação -->
      <div class="step" id="step2">
        <h2>Área de Atuação</h2>
        <select id="area_atuacao" name="area_atuacao" required>
          <option value="">Selecione...</option>
          <?php
            foreach ($areas as $area) {
                echo "<option value=\"" . htmlspecialchars($area) . "\">" . htmlspecialchars($area) . "</option>";
            }
          ?>
        </select>
        <div class="small-text">Selecione sua área de atuação.</div>
        <div class="buttons">
          <button type="button" onclick="prevStep()">Voltar</button>
          <button type="button" onclick="nextStep()">Próximo</button>
        </div>
      </div>
      
      <!-- step3: Seleção de Oficina -->
      <div class="step" id="step3">
        <h2>Selecione uma Oficina</h2>
        <p>Escolha uma oficina disponível para sua área de atuação. (Apenas uma oficina pode ser selecionada)</p>
        <div id="oficinasContainer"></div>
        <div class="buttons">
          <button type="button" onclick="prevStep()">Voltar</button>
          <button type="button" onclick="nextStep()">Próximo</button>
        </div>
      </div>
      
      <!-- step4: Revisão e Agradecimento -->
      <div class="step" id="step4">
        <h2>Revisão e Agradecimento</h2>
        <div id="review"></div>
        <div class="buttons">
          <button type="button" onclick="prevStep()">Voltar para Corrigir</button>
          <button type="submit">Finalizar e Confirmar Inscrição</button>
        </div>
      </div>
      
      <!-- step5: Consulta de Inscrição -->
      <div class="step" id="step5">
        <h2>Consultar Inscrição</h2>
        <p>Digite seu CPF para consultar (apenas dígitos, formatação automática):</p>
        <input type="text" id="cpfConsulta" placeholder="000.000.000-00" oninput="this.value = formatCPF(this.value)">
        <div class="buttons">
          <button type="button" onclick="closeConsulta()">Voltar</button>
          <button type="button" onclick="consultarInscricao()">Consultar</button>
        </div>
        <div id="consultaResultado" style="margin-top:20px;"></div>
      </div>
      
    </form>
  </div>
  
  <div class="contact-wrapper">
    <button class="contact-btn" onclick="window.location.href='contact.php'">Contato</button>
  </div>
  
  <?php include 'footer.php'; ?>
  
  <script>
    /***********************************************
     * ARRAYS ORIGINAIS PARA AWESOMPLETE
     ***********************************************/
    const escolasJSOriginal = <?php echo json_encode($escolas); ?>;
    // Não usamos formacoes, campo removido.
    
    /***********************************************
     * FUNÇÃO DE NORMALIZAÇÃO PARA COMPARAÇÃO
     ***********************************************/
    function normalize(str) {
      return str
        .normalize("NFD")
        .replace(/\p{Diacritic}/gu, "")
        .replace(/\s+/g, " ")
        .trim()
        .toLowerCase();
    }

    const escolasJS = escolasJSOriginal.map(e => normalize(e));

    let currentStep = 0;
    let cpfExists = false; 
    const steps = document.querySelectorAll('.step');

    /***********************************************
     * VALIDAR CAMPOS OBRIGATÓRIOS
     ***********************************************/
    function validateCurrentStep() {
      const requiredFields = steps[currentStep].querySelectorAll('[required]');
      for (let field of requiredFields) {
        if(!field.value.trim()) {
          alert("Preencha o campo: " + (field.placeholder || field.name));
          return false;
        }
        if(currentStep === 1 && field.id === 'cpf') {
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

    /***********************************************
     * nextStep() - Controle de Steps
     ***********************************************/
    function nextStep() {
      if(!validateCurrentStep()) return;
      
      if(currentStep === 1) {
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
        document.getElementById('nome').value = toTitleCase(document.getElementById('nome').value);
        document.getElementById('escola').value = toTitleCase(document.getElementById('escola').value);
        document.getElementById('email').value = document.getElementById('email').value.toLowerCase();
      }
      
      if(currentStep === 2) {
        loadOficinas();
      }
      
      if(currentStep === 3) {
        const radio = document.querySelector('input[name="oficina"]:checked');
        if(!radio) {
          alert("Selecione uma oficina!");
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
      if(currentStep === 5) {
        currentStep = 0;
        showStep(0);
        return;
      }
      if(currentStep > 0) {
        currentStep--;
        showStep(currentStep);
      }
    }

    function showStep(n) {
      steps.forEach((step, i) => {
        step.classList.toggle('active', i === n);
      });
    }

    /***********************************************
     * STEP5 - CONSULTA
     ***********************************************/
    function startConsulta() {
      currentStep = 5;
      showStep(5);
    }
    function closeConsulta() {
      currentStep = 0;
      showStep(0);
    }

    function consultarInscricao() {
      let cpf = document.getElementById('cpfConsulta').value;
      if(cpf.length !== 14) {
        alert("CPF incompleto. Ex.: 021.218.213-76");
        return;
      }
      fetch('consulta.php?cpf=' + encodeURIComponent(cpf))
        .then(resp => resp.json())
        .then(data => {
          const divRes = document.getElementById('consultaResultado');
          if(data.found) {
            let html = `
              <h3>Inscrição Encontrada</h3>
              <table id="reviewTable">
                <tr><th>Campo</th><th>Valor</th></tr>
                <tr><td>Nome</td><td>${data.nome}</td></tr>
                <tr><td>CPF</td><td>${data.cpf}</td></tr>
                <tr><td>Email</td><td>${data.email}</td></tr>
                <tr><td>Telefone</td><td>${data.telefone}</td></tr>
                <tr><td>Escola de Atuação</td><td>${data.escola}</td></tr>
                <tr><td>Área de Atuação</td><td>${data.area_atuacao}</td></tr>
                <tr><td>Oficina</td><td>${data.oficina_desc}</td></tr>
                <tr><td>Escola da Oficina</td><td>${data.oficina_escola}</td></tr>
                <tr><td>Endereço da Oficina</td><td>${data.oficina_endereco || ''}</td></tr>
              </table>
            `;
            if(data.oficina_endereco) {
              const encodedAddr = encodeURIComponent(data.oficina_endereco);
              html += `
                <h4>Localização no Google Maps</h4>
                <iframe 
                  width="100%"
                  height="300"
                  style="border:1px solid #ccc;"
                  src="https://www.google.com/maps?q=${encodedAddr}&output=embed"
                  allowfullscreen
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              `;
            }
            html += `
              <div style="margin-top: 15px;">
                <button onclick="window.print()">Imprimir</button>
                ${data.oficina_endereco ? `<button onclick="window.open('https://www.google.com/maps?q=${encodeURIComponent(data.oficina_endereco)}', '_blank')">Abrir no Google Maps</button>` : ''}
              </div>
            `;
            divRes.innerHTML = html;
          } else {
            divRes.innerHTML = `<p style="color:red;">Nenhuma inscrição encontrada para esse CPF.</p>`;
          }
        })
        .catch(err => {
          alert("Erro ao consultar inscrição.");
          console.error(err);
        });
    }

    /***********************************************
     * populateReview() - Step4
     ***********************************************/
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

    /***********************************************
     * checkCPFExists() - AJAX
     ***********************************************/
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

    /***********************************************
     * Funções de validação e formatação
     ***********************************************/
    function validateCPF(cpfStr) {
      let cpf = cpfStr.replace(/\D/g, '');
      if(cpf.length !== 11) return false;
      if(/^(\d)\1+$/.test(cpf)) return false;
      let add = 0;
      for(let i=0; i<9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
      let rev = 11 - (add % 11);
      if(rev === 10 || rev === 11) rev = 0;
      if(rev !== parseInt(cpf.charAt(9))) return false;
      add = 0;
      for(let i=0; i<10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
      rev = 11 - (add % 11);
      if(rev === 10 || rev === 11) rev = 0;
      if(rev !== parseInt(cpf.charAt(10))) return false;
      return true;
    }

    function formatCPF(value) {
      let v = value.replace(/\D/g,'');
      if(v.length > 11) v = v.slice(0,11);
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
      let v = value.replace(/\D/g,'');
      if(v.length > 11) v = v.slice(0,11);
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

    function toTitleCase(str) {
      return str.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
      });
    }

    /***********************************************
     * Carregar e atualizar oficinas
     ***********************************************/
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

    function updateOficinas() {
      const area = document.getElementById('area_atuacao').value;
      fetch('get_oficinas.php?area=' + encodeURIComponent(area))
        .then(r => r.json())
        .then(data => {
          data.forEach(of => {
            let radio = document.getElementById('oficina_' + of.id);
            if(radio) {
              let label = document.querySelector('label[for="oficina_' + of.id + '"]');
              if(label) {
                label.innerHTML = `${of.descricao} - ${of.vagas} Vagas - ${of.horas}`;
                radio.disabled = (of.vagas <= 0);
                radio.dataset.escola = of.escola || '';
                radio.dataset.endereco = of.endereco || '';
              }
            }
          });
        })
        .catch(err => console.error(err));
    }

    // Se o navegador suportar SSE, usamos EventSource para atualizações em tempo real
    if (!!window.EventSource) {
      var source = new EventSource('sse_vagas.php');
      source.onmessage = function(e) {
           updateOficinas();
      };
    } else {
      setInterval(() => {
        if(currentStep === 3) {
          const c = document.getElementById('oficinasContainer');
          if(c.childElementCount > 0) {
            updateOficinas();
          }
        }
      }, 10000);
    }

    window.addEventListener('load', () => {
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
