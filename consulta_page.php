<?php
// consulta_page.php
$config = include 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Consultar Inscrição - <?php echo nl2br(string: htmlspecialchars($config['event_name'])); ?></title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
    .btn { background: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none; margin: 10px; display: inline-block; }
    .btn:hover { background: #0056b3; }
    .error { color: red; }
    #reviewTable { width: 100%; border-collapse: collapse; margin-top: 15px; }
    #reviewTable th, #reviewTable td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    #reviewTable th { background: #007bff; color: #fff; }
  </style>
</head>
<body>
<div class="container">
  <?php if(!empty($config['banner_url'])): ?>
    <img src="<?php echo htmlspecialchars($config['banner_url']); ?>" alt="Banner" style="max-width:100%; height:auto; margin-bottom:20px;">
  <?php endif; ?>
  <h2>Consultar Inscrição</h2>
  <p>Digite seu CPF para consultar (apenas dígitos, formatação automática):</p>
  <input type="text" id="cpfConsulta" placeholder="000.000.000-00" oninput="formatCPF(this)">
  <div>
    <button class="btn" onclick="consultarInscricao()">Consultar</button>
    <button class="btn" onclick="window.location.href='index.php'">Voltar</button>
  </div>
  <div id="consultaResultado" style="margin-top:20px; text-align:left;"></div>
</div>

<script>
function formatCPF(el) {
  let v = el.value.replace(/\D/g,'');
  if(v.length > 11) v = v.slice(0,11);
  if(v.length >= 4 && v.length < 7) {
    v = v.replace(/(\d{3})(\d+)/, "$1.$2");
  } else if(v.length >= 7 && v.length < 11) {
    v = v.replace(/(\d{3})(\d{3})(\d+)/, "$1.$2.$3");
  } else if(v.length === 11) {
    v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
  }
  el.value = v;
}

function consultarInscricao() {
  const cpf = document.getElementById('cpfConsulta').value;
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
        if(data.opening_address) {
          html += `<p><strong>Local de Abertura:</strong> ${data.opening_address}</p>`;
        }
        if(data.contact_info) {
          html += `<p><strong>Informações de Contato:</strong><br>${data.contact_info.replace(/\n/g, '<br>')}</p>`;
        }
        // Botão para imprimir via print_clean
        // Precisamos do ID do registro
        if(data.id) {
          html += `
            <div style="margin-top: 15px;">
              <button class="btn" onclick="window.location.href='print_clean.php?id=${data.id}'">Imprimir</button>
            </div>
          `;
        } else {
          // Se não tiver id, podemos imprimir por CPF
          html += `
            <div style="margin-top: 15px;">
              <button class="btn" onclick="window.location.href='print_clean.php?cpf=${encodeURIComponent(data.cpf_numeric)}'">Imprimir</button>
            </div>
          `;
        }
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
</script>
</body>
</html>
