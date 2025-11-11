<?php
/**
 * Bootstrap - Inicialização do sistema
 * Carrega todas as bibliotecas necessárias
 */

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carrega configurações
require_once __DIR__ . '/config.php';

// Carrega autoloader (se necessário)
spl_autoload_register(function ($class) {
    $prefix = 'Lib\\';
    $base_dir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Carrega as bibliotecas principais (sem namespace para uso direto)
require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Files.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Schema.php';
require_once __DIR__ . '/auth.php';

// Função auxiliar para limpar strings (mantida para compatibilidade)
if (!function_exists('limpar')) {
    function limpar($valor) {
        return Helper::limpar($valor);
    }
}

// Função auxiliar para validar data
if (!function_exists('validateDate')) {
    function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

