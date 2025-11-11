<?php
/**
 * Script de Migração - Descubra Muriaé
 * Garante que o schema normalizado esteja criado e popula dados iniciais
 */

require_once __DIR__ . '/../lib/bootstrap.php';

$db = new Database();
$pdo = $db->connect();

try {
    // Garantir que o schema normalizado esteja criado
    Schema::ensureNormalizedSchema($pdo);
    
    echo "✓ Schema normalizado verificado/criado\n";

    // Limpar dados antigos de teste (usando schema normalizado)
    try {
        // Limpar pessoas de teste
        $pdo->exec("DELETE FROM pessoa WHERE email LIKE '%@demo.local' OR email = 'admin@descubramuriae.local'");
        $pdo->exec("DELETE FROM pessoa WHERE cpf = '11144477735'");
        
        // Limpar empresas de teste
        $pdo->exec("DELETE FROM empresa WHERE cnpj = '11222333000181'");
        
        // Limpar administradores de teste
        $pdo->exec("DELETE FROM administradores WHERE email = 'admin@descubramuriae.local'");
        
        echo "✓ Dados antigos de teste removidos\n";
    } catch (\Exception $e) {
        echo "⚠ Aviso ao limpar dados antigos: " . $e->getMessage() . "\n";
    }

    // Seeds (usuários de teste) - Schema normalizado
    $hash = Helper::hashSenha('Teste@123');
    $hashAdmin = Helper::hashSenha('Admin@123');

    // PF demo - CPF válido: 111.444.777-35
    $cpfTeste = '11144477735';
    $cpfFormatado = Helper::formatarCPF($cpfTeste);
    
    try {
        $pdo->beginTransaction();
        
        // Criar pessoa
        $stmt = $pdo->prepare("INSERT INTO pessoa (nome, cpf, email, ativo) VALUES (?, ?, ?, 1)");
        $stmt->execute(['Usuário PF Demo', $cpfTeste, 'pf@demo.local']);
        $pessoaId = (int)$pdo->lastInsertId();
        
        // Obter tipo CONT (Contribuinte Normativo)
        $stmt = $pdo->prepare("SELECT usuario_tipo_id FROM usuario_tipo WHERE codigo = 'CONT' LIMIT 1");
        $stmt->execute();
        $tipoRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        $tipoId = $tipoRow ? (int)$tipoRow['usuario_tipo_id'] : 3;
        
        // Criar usuário
        $stmt = $pdo->prepare("INSERT INTO usuario (pessoa_id, login, senha_hash, usuario_tipo_id, ativo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$pessoaId, 'pf@demo.local', $hash, $tipoId]);
        
        $pdo->commit();
        echo "✓ Usuário PF criado: {$cpfFormatado}\n";
    } catch (\Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "⚠ Erro ao criar PF: " . $e->getMessage() . "\n";
    }

    // PJ demo - CNPJ válido: 11.222.333/0001-81
    $cnpjTeste = '11222333000181';
    $cnpjFormatado = Helper::formatarCNPJ($cnpjTeste);
    
    try {
        $pdo->beginTransaction();
        
        // Criar pessoa para PJ
        $stmt = $pdo->prepare("INSERT INTO pessoa (nome, email, ativo) VALUES (?, ?, 1)");
        $stmt->execute(['Empresa PJ Demo', 'pj@demo.local']);
        $pessoaId = (int)$pdo->lastInsertId();
        
        // Obter tipo ANUNC (Anunciante)
        $stmt = $pdo->prepare("SELECT usuario_tipo_id FROM usuario_tipo WHERE codigo = 'ANUNC' LIMIT 1");
        $stmt->execute();
        $tipoRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        $tipoId = $tipoRow ? (int)$tipoRow['usuario_tipo_id'] : 1;
        
        // Criar usuário
        $stmt = $pdo->prepare("INSERT INTO usuario (pessoa_id, login, senha_hash, usuario_tipo_id, ativo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$pessoaId, 'pj@demo.local', $hash, $tipoId]);
        
        // Criar empresa
        $stmt = $pdo->prepare("INSERT INTO empresa (cnpj, nome_social, email, ativo) VALUES (?, ?, ?, 1)");
        $stmt->execute([$cnpjTeste, 'Empresa PJ Demo', 'pj@demo.local']);
        
        $pdo->commit();
        echo "✓ Usuário PJ criado: {$cnpjFormatado}\n";
    } catch (\Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "⚠ Erro ao criar PJ: " . $e->getMessage() . "\n";
    }

    // Admin demo
    $emailAdmin = 'admin@descubramuriae.local';
    try {
        $stmt = $pdo->prepare("INSERT INTO administradores (nome, email, senha, ativo) VALUES (?, ?, ?, 1)");
        $stmt->execute(['Administrador Demo', $emailAdmin, $hashAdmin]);
        echo "✓ Administrador criado: {$emailAdmin}\n";
    } catch (\Exception $e) {
        echo "⚠ Erro ao criar Admin: " . $e->getMessage() . "\n";
    }

    echo "\n========================================\n";
    echo "Migração executada com sucesso!\n";
    echo "========================================\n\n";
    echo "Usuários de teste disponíveis:\n\n";
    echo "PESSOA FÍSICA (PF):\n";
    echo "  CPF: {$cpfFormatado}\n";
    echo "  Senha: Teste@123\n\n";
    echo "PESSOA JURÍDICA (PJ):\n";
    echo "  CNPJ: {$cnpjFormatado}\n";
    echo "  Senha: Teste@123\n\n";
    echo "ADMINISTRADOR:\n";
    echo "  Email: {$emailAdmin}\n";
    echo "  Senha: Admin@123\n\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Erro ao migrar: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}


