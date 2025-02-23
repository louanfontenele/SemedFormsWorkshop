<?php
// db.php
$config = include 'config.php';

if ($config['db_driver'] === 'mysql') {
    // Conexão MySQL
    $dsn = "mysql:host=" . $config['mysql_host'] . ";dbname=" . $config['mysql_db'] . ";charset=utf8";
    try {
        $db = new PDO($dsn, $config['mysql_user'], $config['mysql_pass']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        die("Erro ao conectar com o MySQL: " . $e->getMessage());
    }
} else {
    // Conexão SQLite
    $db_file = __DIR__ . '/data.db';
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        die("Erro ao conectar com o banco de dados SQLite: " . $e->getMessage());
    }
}

// Cria a tabela de cadastros
$db->exec("CREATE TABLE IF NOT EXISTS registrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    cpf TEXT UNIQUE,
    email TEXT,
    telefone TEXT,
    escola TEXT,
    formacao TEXT,
    area_atuacao TEXT,
    oficina INTEGER
)");

// Cria a tabela de oficinas
$db->exec("CREATE TABLE IF NOT EXISTS oficinas (
    id INTEGER PRIMARY KEY,
    descricao TEXT,
    vagas INTEGER,
    areas TEXT,
    horas TEXT,
    escola TEXT,
    endereco TEXT
)");

// Se a tabela de oficinas estiver vazia, insere os registros do arquivo oficinas.php
$count = $db->query("SELECT COUNT(*) FROM oficinas")->fetchColumn();
if ($count == 0) {
    $oficinas = include 'oficinas.php';
    $stmt = $db->prepare("INSERT INTO oficinas (id, descricao, vagas, areas, horas, escola, endereco) 
                          VALUES (:id, :descricao, :vagas, :areas, :horas, :escola, :endereco)");
    foreach ($oficinas as $oficina) {
        $stmt->execute([
            ':id' => $oficina['id'],
            ':descricao' => $oficina['descricao'],
            ':vagas' => $oficina['vagas'],
            ':areas' => $oficina['areas'],
            ':horas' => $oficina['horas'],
            ':escola' => $oficina['escola'],
            ':endereco' => $oficina['endereco']
        ]);
    }
}
?>
