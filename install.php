<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';
$config = include 'config.php';

$lockFile = 'lock-install';

// Se o arquivo lock-install existir, impede nova instalação
if(file_exists($lockFile)) {
    die("A instalação já foi realizada. Para reinstalar, remova o arquivo 'lock-install'.");
}

$db_driver = isset($config['db_driver']) ? $config['db_driver'] : 'sqlite';

try {
    if($db_driver === 'mysql') {
        // Cria as tabelas no MySQL – utilizando a coluna 'areas'
        $db->exec("CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome TEXT,
            cpf TEXT UNIQUE,
            email TEXT,
            telefone TEXT,
            email TEXT,
            escola TEXT,
            formacao TEXT,
            area_atuacao TEXT,
            oficina INT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS oficinas (
            id INT PRIMARY KEY,
            descricao TEXT,
            vagas INT,
            areas TEXT,
            horas VARCHAR(50),
            escola TEXT,
            endereco TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    } else {
        // Cria as tabelas no SQLite
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
        $db->exec("CREATE TABLE IF NOT EXISTS oficinas (
            id INTEGER PRIMARY KEY,
            descricao TEXT,
            vagas INTEGER,
            areas TEXT,
            horas TEXT,
            escola TEXT,
            endereco TEXT
        )");
    }
    
    // Cria o arquivo lock-install para impedir nova instalação
    file_put_contents($lockFile, "Instalado em " . date("Y-m-d H:i:s"));
    echo "Instalação realizada com sucesso!";
} catch(Exception $e) {
    die("Erro durante a instalação: " . $e->getMessage());
}
?>
