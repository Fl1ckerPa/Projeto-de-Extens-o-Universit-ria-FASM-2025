<?php

/**
 * Arquivo de Configuração
 * Adaptado do AtomPHP para uso sem MVC
 */

// Configurações do banco de dados
define('DB_DRIVE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_DATABASE', 'descubra_muriae');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Configurações do projeto
define('BASE_PATH', dirname(__DIR__));
define('UPLOADS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');

// Configurações de upload
define('FILE_MAXSIZE', 10); // MB
define('FILE_ALLOWEDTYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain'
]);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

