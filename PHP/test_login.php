<?php
/**
 * Script de teste para verificar login PF/PJ
 */
require_once __DIR__ . '/../lib/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Teste de Login - Verificação do Banco</h2>";

$db = new Database();

// Listar usuários PF
echo "<h3>Usuários PF (Pessoa Física)</h3>";
try {
    $pfUsers = $db->table('usuarios_pf')->findAll();
    if (empty($pfUsers)) {
        echo "<p style='color: red;'>Nenhum usuário PF encontrado. Execute o migrate.php primeiro!</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Email</th></tr>";
        foreach ($pfUsers as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome']}</td>";
            echo "<td>{$user['cpf']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar PF: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

// Listar usuários PJ
echo "<h3>Usuários PJ (Pessoa Jurídica)</h3>";
try {
    $pjUsers = $db->table('usuarios_pj')->findAll();
    if (empty($pjUsers)) {
        echo "<p style='color: red;'>Nenhum usuário PJ encontrado. Execute o migrate.php primeiro!</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nome</th><th>CNPJ</th><th>Email</th></tr>";
        foreach ($pjUsers as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome']}</td>";
            echo "<td>{$user['cnpj']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar PJ: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

// Teste de busca simulando login
echo "<h3>Teste de Busca (simulando login)</h3>";

// Teste PF
$cpfTeste = '11144477735';
$cpfFormatado = Helper::formatarCPF($cpfTeste);
echo "<p><strong>Testando CPF:</strong> {$cpfFormatado} (sem máscara: {$cpfTeste})</p>";

$usuarioPF = $db->table('usuarios_pf')->where('cpf', $cpfFormatado)->first();
if (!$usuarioPF) {
    $usuarioPF = $db->table('usuarios_pf')->where('cpf', $cpfTeste)->first();
}

if ($usuarioPF) {
    echo "<p style='color: green;'>✓ Usuário PF encontrado: {$usuarioPF['nome']}</p>";
    echo "<p>CPF no banco: {$usuarioPF['cpf']}</p>";
    
    // Testar senha
    $senhaTeste = 'Teste@123';
    $verifica = Helper::verificarSenha($senhaTeste, $usuarioPF['senha']);
    echo "<p>Senha 'Teste@123' válida? " . ($verifica ? '<span style="color: green;">SIM</span>' : '<span style="color: red;">NÃO</span>') . "</p>";
} else {
    echo "<p style='color: red;'>✗ Usuário PF NÃO encontrado!</p>";
}

echo "<hr>";

// Teste PJ
$cnpjTeste = '11222333000181';
$cnpjFormatado = Helper::formatarCNPJ($cnpjTeste);
echo "<p><strong>Testando CNPJ:</strong> {$cnpjFormatado} (sem máscara: {$cnpjTeste})</p>";

$usuarioPJ = $db->table('usuarios_pj')->where('cnpj', $cnpjFormatado)->first();
if (!$usuarioPJ) {
    $usuarioPJ = $db->table('usuarios_pj')->where('cnpj', $cnpjTeste)->first();
}

if ($usuarioPJ) {
    echo "<p style='color: green;'>✓ Usuário PJ encontrado: {$usuarioPJ['nome']}</p>";
    echo "<p>CNPJ no banco: {$usuarioPJ['cnpj']}</p>";
    
    // Testar senha
    $senhaTeste = 'Teste@123';
    $verifica = Helper::verificarSenha($senhaTeste, $usuarioPJ['senha']);
    echo "<p>Senha 'Teste@123' válida? " . ($verifica ? '<span style="color: green;">SIM</span>' : '<span style="color: red;">NÃO</span>') . "</p>";
} else {
    echo "<p style='color: red;'>✗ Usuário PJ NÃO encontrado!</p>";
}

echo "<hr>";
echo "<p><a href='../HTML/login.html'>Voltar para Login</a></p>";

