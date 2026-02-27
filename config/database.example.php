<?php
/**
 * Configuração do banco de dados MySQL
 * 
 * CÓPIA ESTE ARQUIVO PARA: database.php
 * e preencha com os dados do seu servidor.
 */
return [
    'host'      => 'localhost',
    'database'  => 'NOME_DO_BANCO',
    'username'  => 'USUARIO',
    'password'  => 'SENHA',
    'charset'   => 'utf8mb4',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
