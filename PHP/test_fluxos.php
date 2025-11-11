<?php
/**
 * Script de Teste de Fluxos - Descubra Muriaé
 * Testa os principais fluxos do sistema (PF, PJ, Admin)
 * 
 * Acesse: http://localhost:8000/PHP/test_fluxos.php
 */

require_once __DIR__ . '/../lib/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Teste de Fluxos - Descubra Muriaé</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .test-section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
        .test-item { margin: 10px 0; padding: 10px; background: white; border-radius: 4px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🧪 Teste de Fluxos - Descubra Muriaé</h1>
";

$db = new Database();
$pdo = $db->connect();

// Teste 1: Conexão com Banco
echo "<div class='test-section'>";
echo "<h2>1. Teste de Conexão com Banco de Dados</h2>";

try {
    Schema::ensureNormalizedSchema($pdo);
    echo "<div class='test-item success'>✓ Conexão com banco de dados estabelecida com sucesso</div>";
    echo "<div class='test-item info'>Banco: " . DB_DATABASE . "</div>";
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro na conexão: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

// Teste 2: Verificar Tabelas
echo "<h2>2. Verificação de Tabelas</h2>";

$tabelasObrigatorias = [
    'pessoa', 'usuario', 'usuario_tipo', 'empresa', 'curriculo',
    'vaga', 'candidatura', 'status_vaga', 'status_candidatura',
    'categoria_vaga', 'modalidade_trabalho', 'vinculo_contratual',
    'administradores', 'cidade'
];

$tabelasExistentes = [];
$tabelasFaltantes = [];

foreach ($tabelasObrigatorias as $tabela) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            $tabelasExistentes[] = $tabela;
            echo "<div class='test-item success'>✓ Tabela '$tabela' existe</div>";
        } else {
            $tabelasFaltantes[] = $tabela;
            echo "<div class='test-item error'>✗ Tabela '$tabela' NÃO existe</div>";
        }
    } catch (Exception $e) {
        $tabelasFaltantes[] = $tabela;
        echo "<div class='test-item error'>✗ Erro ao verificar '$tabela': " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Teste 3: Verificar Usuários de Teste
echo "<h2>3. Verificação de Usuários de Teste</h2>";

// PF
try {
    $stmt = $pdo->prepare("SELECT p.*, u.usuario_id, u.login FROM pessoa p 
                          INNER JOIN usuario u ON u.pessoa_id = p.pessoa_id 
                          WHERE p.cpf = '11144477735' LIMIT 1");
    $stmt->execute();
    $pf = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pf) {
        echo "<div class='test-item success'>✓ Usuário PF encontrado: " . htmlspecialchars($pf['nome']) . "</div>";
    } else {
        echo "<div class='test-item warning'>⚠ Usuário PF não encontrado. Execute migrate.php</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro ao buscar PF: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// PJ
try {
    $stmt = $pdo->prepare("SELECT * FROM empresa WHERE cnpj = '11222333000181' LIMIT 1");
    $stmt->execute();
    $pj = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pj) {
        echo "<div class='test-item success'>✓ Empresa PJ encontrada: " . htmlspecialchars($pj['nome_social']) . "</div>";
    } else {
        echo "<div class='test-item warning'>⚠ Empresa PJ não encontrada. Execute migrate.php</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro ao buscar PJ: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Admin
try {
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = 'admin@descubramuriae.local' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "<div class='test-item success'>✓ Administrador encontrado: " . htmlspecialchars($admin['nome']) . "</div>";
    } else {
        echo "<div class='test-item warning'>⚠ Administrador não encontrado. Execute migrate.php</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro ao buscar Admin: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Teste 4: Verificar Dados Iniciais
echo "<h2>4. Verificação de Dados Iniciais</h2>";

// Usuario Tipo
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuario_tipo");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='test-item info'>Tipos de usuário cadastrados: " . $result['total'] . "</div>";
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Status Vaga
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM status_vaga");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='test-item info'>Status de vagas cadastrados: " . $result['total'] . "</div>";
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Status Candidatura
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM status_candidatura");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='test-item info'>Status de candidaturas cadastrados: " . $result['total'] . "</div>";
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Teste 5: Testar Login (simulado)
echo "<h2>5. Teste de Validação de Login (Simulado)</h2>";

// Teste PF
try {
    $cpf = '11144477735';
    $stmt = $pdo->prepare("SELECT u.usuario_id, u.senha_hash, p.nome, p.email, p.pessoa_id, ut.codigo AS role_codigo
                          FROM pessoa p
                          INNER JOIN usuario u ON u.pessoa_id = p.pessoa_id
                          INNER JOIN usuario_tipo ut ON ut.usuario_tipo_id = u.usuario_tipo_id
                          WHERE p.cpf = ? AND u.ativo = 1 AND p.ativo = 1
                          LIMIT 1");
    $stmt->execute([$cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "<div class='test-item success'>✓ Login PF: Usuário encontrado no banco</div>";
        echo "<div class='test-item info'>Nome: " . htmlspecialchars($usuario['nome']) . "</div>";
        echo "<div class='test-item info'>Role: " . htmlspecialchars($usuario['role_codigo']) . "</div>";
        
        // Testar senha
        if (Helper::verificarSenha('Teste@123', $usuario['senha_hash'])) {
            echo "<div class='test-item success'>✓ Senha válida para PF</div>";
        } else {
            echo "<div class='test-item error'>✗ Senha inválida para PF</div>";
        }
    } else {
        echo "<div class='test-item error'>✗ Login PF: Usuário não encontrado</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro ao testar login PF: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Teste PJ
try {
    $cnpj = '11222333000181';
    $stmt = $pdo->prepare("SELECT empresa_id, email, nome_social FROM empresa WHERE cnpj = ? AND ativo = 1 LIMIT 1");
    $stmt->execute([$cnpj]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($empresa) {
        echo "<div class='test-item success'>✓ Login PJ: Empresa encontrada no banco</div>";
        echo "<div class='test-item info'>Nome: " . htmlspecialchars($empresa['nome_social']) . "</div>";
        
        // Buscar usuário associado
        $stmt = $pdo->prepare("SELECT u.usuario_id, u.senha_hash, p.nome, p.email, p.pessoa_id, ut.codigo AS role_codigo
                              FROM usuario u
                              INNER JOIN pessoa p ON p.pessoa_id = u.pessoa_id
                              INNER JOIN usuario_tipo ut ON ut.usuario_tipo_id = u.usuario_tipo_id
                              WHERE u.login = ? AND u.ativo = 1 AND p.ativo = 1
                              LIMIT 1");
        $stmt->execute([$empresa['email']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            echo "<div class='test-item success'>✓ Usuário associado à empresa encontrado</div>";
            if (Helper::verificarSenha('Teste@123', $usuario['senha_hash'])) {
                echo "<div class='test-item success'>✓ Senha válida para PJ</div>";
            } else {
                echo "<div class='test-item error'>✗ Senha inválida para PJ</div>";
            }
        } else {
            echo "<div class='test-item warning'>⚠ Usuário não encontrado para email da empresa</div>";
        }
    } else {
        echo "<div class='test-item error'>✗ Login PJ: Empresa não encontrada</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro ao testar login PJ: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Teste Admin
try {
    $email = 'admin@descubramuriae.local';
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ? AND ativo = 1 LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<div class='test-item success'>✓ Login Admin: Administrador encontrado</div>";
        if (Helper::verificarSenha('Admin@123', $admin['senha'])) {
            echo "<div class='test-item success'>✓ Senha válida para Admin</div>";
        } else {
            echo "<div class='test-item error'>✗ Senha inválida para Admin</div>";
        }
    } else {
        echo "<div class='test-item error'>✗ Login Admin: Administrador não encontrado</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>✗ Erro ao testar login Admin: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Resumo
echo "<h2>📊 Resumo</h2>";
echo "<div class='test-section'>";
echo "<div class='test-item info'><strong>Tabelas existentes:</strong> " . count($tabelasExistentes) . " / " . count($tabelasObrigatorias) . "</div>";
if (count($tabelasFaltantes) > 0) {
    echo "<div class='test-item warning'><strong>Tabelas faltantes:</strong> " . implode(', ', $tabelasFaltantes) . "</div>";
    echo "<div class='test-item'><a href='migrate.php' class='btn'>Executar Migração</a></div>";
}
echo "</div>";

echo "<h2>🔗 Links Úteis</h2>";
echo "<div class='test-section'>";
echo "<a href='migrate.php' class='btn'>Executar Migração</a>";
echo "<a href='db_check.php' class='btn'>Verificar Conexão DB</a>";
echo "<a href='../HTML/login.html' class='btn'>Página de Login</a>";
echo "<a href='../HTML/index.html' class='btn'>Página Inicial</a>";
echo "</div>";

echo "</div></body></html>";

