<?php
// config.php
date_default_timezone_set('America/Sao_Paulo');

return [
    'db_driver' => 'sqlite', // ou 'mysql'
    'mysql_host' => 'localhost',
    'mysql_db'   => 'semedforms',
    'mysql_user' => 'root',
    'mysql_pass' => '',

    // Período de inscrições
    'registration_start' => '2025-02-20 00:00:00',
    'registration_end'   => '2025-02-19 23:59:59',

    // Nome do evento
    'event_name'         => "Jornada Pedagógica 2025\nEducação Básica: cenários, desafios e possibilidades",

    // Banner da jornada (URL de uma imagem)
    'banner_url'         => "https://exemplo.com/imagens/jornada_banner.jpg",

    // Mensagem de boas-vindas
    'welcome_message'    => "Bem-vindo(a) à nossa Jornada Pedagógica! Confira as informações abaixo para realizar sua inscrição. Após confirmar, não será possível alterar seus dados.",

    // Dados de contato
    'contact_info'       => "(98) 9 9231-0657 — Whatsapp (Elinalva)\n(98) 9 8903-5895 — Whatsapp (Josy)\n(98) 9 9166-6413 — Whatsapp (Cleane Macedo)",

    // Local de abertura fixo
    'opening_address'    => "Igreja Evangélica Assembleia De Deus (Templo Central)"
];
