<?php
require_once __DIR__ . '/../lib/bootstrap.php';

try {
    $db = new Database();
    $pdo = $db->connect();
    $stmt = $pdo->query('SELECT 1');
    $ok = $stmt !== false ? 'OK' : 'FALHA';
    header('Content-Type: text/plain; charset=utf-8');
    echo "Conexão ao banco: {$ok}\n";
    echo 'Driver: ' . DB_DRIVE . "\n";
    echo 'Host: ' . DB_HOST . ':' . DB_PORT . "\n";
    echo 'Base: ' . DB_DATABASE . "\n";
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erro na conexão: ' . $e->getMessage();
}


