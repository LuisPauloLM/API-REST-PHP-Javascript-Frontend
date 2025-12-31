<?php
// index.php

declare(strict_types=1);

// Carregar variáveis de ambiente
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Pula comentários
        if (strpos($line, '=') === false) continue; // Pula linhas sem =

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove aspas se existirem
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

// Ponto de entrada da API
// Configura o roteador e inicia a aplicação
require_once(__DIR__ . '/src/routes/Roteador.php');

// Inicia o roteador
(new Roteador())->start();
