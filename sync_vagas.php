<?php
// sync_vagas.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

require 'db.php';
$config = include 'config.php';

// Carrega os dados padrão das oficinas do arquivo (definidos originalmente)
$defaultOficinas = include 'oficinas.php';
$defaultMap = [];
foreach ($defaultOficinas as $oficina) {
    // Usamos o id como chave; certifique-se de que os IDs são únicos
    $defaultMap[$oficina['id']] = $oficina;
}

// Consulta as oficinas atualmente no banco de dados
$stmt = $db->query("SELECT id, descricao, vagas FROM oficinas ORDER BY id ASC");
$dbOficinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Conta as inscrições para cada oficina
$registrationCounts = [];
$stmtCount = $db->prepare("SELECT COUNT(*) as count FROM registrations WHERE oficina = :id");
foreach ($dbOficinas as $oficina) {
    $stmtCount->execute([':id' => $oficina['id']]);
    $countRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
    $registrationCounts[$oficina['id']] = $countRow ? (int)$countRow['count'] : 0;
}

// Se o formulário foi submetido para atualizar (individual ou todas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_all'])) {
        // Atualiza todas as oficinas: para cada oficina, calcula o valor correto de vagas
        foreach ($dbOficinas as $oficina) {
            $id = $oficina['id'];
            $defaultVacancies = isset($defaultMap[$id]) ? (int)$defaultMap[$id]['vagas'] : 0;
            $regCount = isset($registrationCounts[$id]) ? (int)$registrationCounts[$id] : 0;
            $newVacancies = $defaultVacancies - $regCount;
            if ($newVacancies < 0) {
                $newVacancies = 0;
            }
            $stmtUpdate = $db->prepare("UPDATE oficinas SET vagas = :vagas WHERE id = :id");
            $stmtUpdate->execute([':vagas' => $newVacancies, ':id' => $id]);
        }
        header("Location: sync_vagas.php?msg=Todas+as+oficinas+atualizadas");
        exit;
    } elseif (isset($_POST['update_id'])) {
        // Atualiza apenas a oficina cujo id foi enviado
        $updateId = (int)$_POST['update_id'];
        $defaultVacancies = isset($defaultMap[$updateId]) ? (int)$defaultMap[$updateId]['vagas'] : 0;
        $regCount = isset($registrationCounts[$updateId]) ? (int)$registrationCounts[$updateId] : 0;
        $newVacancies = $defaultVacancies - $regCount;
        if ($newVacancies < 0) {
            $newVacancies = 0;
        }
        $stmtUpdate = $db->prepare("UPDATE oficinas SET vagas = :vagas WHERE id = :id");
        $stmtUpdate->execute([':vagas' => $newVacancies, ':id' => $updateId]);
        header("Location: sync_vagas.php?msg=Oficina+ID+{$updateId}+atualizada");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Sincronizar Vagas - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background: #007bff;
            color: #fff;
        }
        .btn {
            background: #28a745;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover {
            background: #218838;
        }
        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sincronizar Vagas</h1>
        <?php if (isset($_GET['msg'])): ?>
            <div class="message"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <p>
            Esta página mostra para cada oficina o número padrão de vagas (definido em <code>oficinas.php</code>),
            quantas inscrições já foram realizadas e a quantidade atual de vagas (armazenada no banco).<br>
            Você pode atualizar individualmente ou atualizar todas as oficinas para sincronizar o número de vagas.
        </p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descrição</th>
                    <th>Vagas Padrão</th>
                    <th>Inscrições (Contagem)</th>
                    <th>Vagas Calculadas<br>(Padrão - Inscritos)</th>
                    <th>Vagas no Banco</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dbOficinas as $oficina): 
                    $id = $oficina['id'];
                    $defaultVacancies = isset($defaultMap[$id]) ? (int)$defaultMap[$id]['vagas'] : 0;
                    $regCount = isset($registrationCounts[$id]) ? (int)$registrationCounts[$id] : 0;
                    $calculatedVacancies = $defaultVacancies - $regCount;
                    if ($calculatedVacancies < 0) $calculatedVacancies = 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($id); ?></td>
                    <td><?php echo htmlspecialchars($oficina['descricao']); ?></td>
                    <td><?php echo $defaultVacancies; ?></td>
                    <td><?php echo $regCount; ?></td>
                    <td><?php echo $calculatedVacancies; ?></td>
                    <td><?php echo htmlspecialchars($oficina['vagas']); ?></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="update_id" value="<?php echo $id; ?>">
                            <button type="submit" class="btn">Atualizar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <form method="POST">
            <button type="submit" name="update_all" class="btn">Atualizar Todas</button>
        </form>
        <br>
        <a href="admin.php" class="btn">Voltar ao Dashboard</a>
    </div>
</body>
</html>
