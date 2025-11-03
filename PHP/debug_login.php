<?php
/**
 * Script de debug para login
 */
require_once __DIR__ . '/../lib/bootstrap.php';

echo "<h2>Debug Login</h2>";

$db = new Database();

// Testar PF
echo "<h3>Testando PF</h3>";
$cpfTeste = '12345678909';
$cpfMascarado = Helper::formatarCPF($cpfTeste);
echo "CPF sem máscara: $cpfTeste<br>";
echo "CPF mascarado: $cpfMascarado<br>";
echo "CPF válido? " . (Helper::validarCPF($cpfTeste) ? 'SIM' : 'NÃO') . "<br><br>";

$usuarioPF = $db->table('usuarios_pf')->where('cpf', $cpfMascarado)->first();
if (!$usuarioPF) {
    $usuarioPF = $db->table('usuarios_pf')->where('cpf', $cpfTeste)->first();
}

if ($usuarioPF) {
    echo "Usuário encontrado: " . $usuarioPF['nome'] . "<br>";
    echo "CPF no banco: " . $usuarioPF['cpf'] . "<br>";
    echo "Hash da senha: " . substr($usuarioPF['senha'], 0, 20) . "...<br>";
    
    $senhaTeste = 'Teste@123';
    $verifica = Helper::verificarSenha($senhaTeste, $usuarioPF['senha']);
    echo "Senha 'Teste@123' válida? " . ($verifica ? 'SIM' : 'NÃO') . "<br>";
} else {
    echo "Usuário NÃO encontrado!<br>";
    echo "Listando todos os usuários PF:<br>";
    $todos = $db->table('usuarios_pf')->findAll();
    foreach ($todos as $u) {
        echo "- ID: {$u['id']}, Nome: {$u['nome']}, CPF: {$u['cpf']}<br>";
    }
}

echo "<hr>";

// Testar PJ
echo "<h3>Testando PJ</h3>";
$cnpjTeste = '12345678000195';
$cnpjMascarado = Helper::formatarCNPJ($cnpjTeste);
echo "CNPJ sem máscara: $cnpjTeste<br>";
echo "CNPJ mascarado: $cnpjMascarado<br>";
echo "CNPJ válido? " . (Helper::validarCNPJ($cnpjTeste) ? 'SIM' : 'NÃO') . "<br><br>";

$usuarioPJ = $db->table('usuarios_pj')->where('cnpj', $cnpjMascarado)->first();
if (!$usuarioPJ) {
    $usuarioPJ = $db->table('usuarios_pj')->where('cnpj', $cnpjTeste)->first();
}

if ($usuarioPJ) {
    echo "Usuário encontrado: " . $usuarioPJ['nome'] . "<br>";
    echo "CNPJ no banco: " . $usuarioPJ['cnpj'] . "<br>";
    echo "Hash da senha: " . substr($usuarioPJ['senha'], 0, 20) . "...<br>";
    
    $senhaTeste = 'Teste@123';
    $verifica = Helper::verificarSenha($senhaTeste, $usuarioPJ['senha']);
    echo "Senha 'Teste@123' válida? " . ($verifica ? 'SIM' : 'NÃO') . "<br>";
} else {
    echo "Usuário NÃO encontrado!<br>";
    echo "Listando todos os usuários PJ:<br>";
    $todos = $db->table('usuarios_pj')->findAll();
    foreach ($todos as $u) {
        echo "- ID: {$u['id']}, Nome: {$u['nome']}, CNPJ: {$u['cnpj']}<br>";
    }
}

