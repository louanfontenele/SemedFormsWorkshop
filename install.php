<?php
require 'db.php';
$config = include 'config.php';

$lockFile = 'lock-install';

// Se o arquivo lock-install existir, impede nova instalação
if(file_exists($lockFile)) {
    die("A instalação já foi realizada. Para reinstalar, remova o arquivo 'lock-install'.");
}

// Verifica qual método de banco está sendo usado (supondo que config.php contenha uma chave 'db_driver' com 'sqlite' ou 'mysql')
$db_driver = isset($config['db_driver']) ? $config['db_driver'] : 'sqlite';

try {
    if($db_driver === 'sqlite') {
        // Cria a tabela de inscrições
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
            series TEXT,
            horas TEXT,
            escola TEXT,
            endereco TEXT
        )");
    } else if($db_driver === 'mysql') {
        // Exemplo para MySQL – adapte conforme necessário
        $db->exec("CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255),
            cpf VARCHAR(20) UNIQUE,
            email VARCHAR(255),
            telefone VARCHAR(20),
            escola VARCHAR(255),
            formacao VARCHAR(255),
            area_atuacao VARCHAR(255),
            oficina INT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $db->exec("CREATE TABLE IF NOT EXISTS oficinas (
            id INT PRIMARY KEY,
            descricao VARCHAR(255),
            vagas INT,
            series VARCHAR(255),
            horas VARCHAR(50),
            escola VARCHAR(255),
            endereco VARCHAR(255)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }
    
    // Cria o arquivo lock-install para impedir nova instalação
    file_put_contents($lockFile, "Instalado em " . date("Y-m-d H:i:s"));
    echo "Instalação realizada com sucesso!";
} catch(Exception $e) {
    die("Erro durante a instalação: " . $e->getMessage());
}
?>
