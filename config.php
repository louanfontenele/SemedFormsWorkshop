<?php
// config.php

// Define o fuso horário para America/Sao_Paulo
date_default_timezone_set('America/Sao_Paulo');


return [
    // Se desejar usar MySQL, altere para 'mysql' e configure as credenciais abaixo.
    'db_driver' => 'sqlite', // 'sqlite' ou 'mysql'
    'mysql_host' => 'localhost',
    'mysql_db'   => 'semedforms',
    'mysql_user' => 'root',
    'mysql_pass' => '',
    
    // Período de inscrições
    'registration_start' => '2025-02-20 00:00:00',
    'registration_end'   => '2025-03-19 23:59:59',
    
    // Mensagem de boas-vindas
    'welcome_message'    => "Bem-vindo ao Sistema de Inscrição para as Oficinas da SEMED.\n\nAqui você encontrará informações sobre as oficinas, os dados de contato da equipe organizadora e as datas de inscrição.\n\nAs inscrições estão abertas do 1 de Março de 2025 até 31 de Março de 2025.",
    
    // Dados de contato (exibidos em vários locais)
    'contact_info'       => "E-mail: organizacao@semed.gov.br\nTelefone: (11) 98765-4321"
];
?>
