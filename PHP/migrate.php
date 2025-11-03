<?php
require_once __DIR__ . '/../lib/bootstrap.php';

$db = new Database();

try {
    // Criar tabela usuarios_pf (Pessoa Física)
    $db->dbUpdate("CREATE TABLE IF NOT EXISTS usuarios_pf (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        cpf VARCHAR(14) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        email VARCHAR(160) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Criar tabela usuarios_pj (Pessoa Jurídica)
    $db->dbUpdate("CREATE TABLE IF NOT EXISTS usuarios_pj (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(160) NOT NULL,
        cnpj VARCHAR(18) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        email VARCHAR(160) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Criar tabela curriculos
    $db->dbUpdate("CREATE TABLE IF NOT EXISTS curriculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(160) NOT NULL,
        endereco VARCHAR(255) NOT NULL,
        telefone VARCHAR(32) NOT NULL,
        email VARCHAR(160) NOT NULL,
        genero VARCHAR(20) NOT NULL,
        estado_civil VARCHAR(20) NULL,
        nascimento DATE NOT NULL,
        escolaridade VARCHAR(100) NOT NULL,
        outros_cursos TEXT NULL,
        foto VARCHAR(255) NULL,
        certificado VARCHAR(255) NULL,
        curriculo VARCHAR(255) NULL,
        experiencias JSON NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Limpar TODOS os dados antigos de teste
    try {
        // Limpar TODOS os usuários de teste (por email)
        $db->dbUpdate("DELETE FROM usuarios_pf WHERE email LIKE '%@demo.local'");
        $db->dbUpdate("DELETE FROM usuarios_pj WHERE email LIKE '%@demo.local'");
        
        // Limpar CPF antigo (12345678909 ou 123.456.789-09) - REMOVER COMPLETAMENTE
        $db->dbUpdate("DELETE FROM usuarios_pf WHERE cpf = '12345678909' OR cpf = '123.456.789-09'");
        
        // Limpar CNPJ antigo (12345678000195 ou 12.345.678/0001-95) - REMOVER COMPLETAMENTE
        $db->dbUpdate("DELETE FROM usuarios_pj WHERE cnpj = '12345678000195' OR cnpj = '12.345.678/0001-95'");
        
        // Garantir que não há outros usuários de teste
        $db->dbUpdate("DELETE FROM usuarios_pf WHERE nome LIKE '%Demo%' OR nome LIKE '%demo%'");
        $db->dbUpdate("DELETE FROM usuarios_pj WHERE nome LIKE '%Demo%' OR nome LIKE '%demo%'");
        
        echo "✓ Todos os usuários de teste antigos foram removidos\n";
    } catch (\Exception $e) {
        // Ignora erro se tabelas não existirem
        echo "⚠ Aviso ao limpar dados antigos: " . $e->getMessage() . "\n";
    }

    // Seeds (usuários de teste) - SEMPRE criar novos
    $hash = Helper::hashSenha('Teste@123');

    // PF demo - usando CPF válido: 11144477735 -> 111.444.777-35
    $cpfTeste = '11144477735';
    $cpfFormatado = Helper::formatarCPF($cpfTeste);
    
    // Remover se já existir e criar novo
    try {
        $db->dbUpdate("DELETE FROM usuarios_pf WHERE cpf = '" . addslashes($cpfFormatado) . "'");
    } catch (\Exception $e) {
        // Ignora
    }
    
    $db->dbInsert(
        "INSERT INTO usuarios_pf (nome, cpf, senha, email) VALUES (?, ?, ?, ?)",
        ['Usuário PF Demo', $cpfFormatado, $hash, 'pf@demo.local']
    );
    echo "✓ Usuário PF criado: {$cpfFormatado}\n";

    // PJ demo - usando CNPJ válido: 11222333000181 -> 11.222.333/0001-81
    $cnpjTeste = '11222333000181';
    $cnpjFormatado = Helper::formatarCNPJ($cnpjTeste);
    
    // Remover se já existir e criar novo
    try {
        $db->dbUpdate("DELETE FROM usuarios_pj WHERE cnpj = '" . addslashes($cnpjFormatado) . "'");
    } catch (\Exception $e) {
        // Ignora
    }
    
    $db->dbInsert(
        "INSERT INTO usuarios_pj (nome, cnpj, senha, email) VALUES (?, ?, ?, ?)",
        ['Empresa PJ Demo', $cnpjFormatado, $hash, 'pj@demo.local']
    );
    echo "✓ Usuário PJ criado: {$cnpjFormatado}\n";

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
} catch (Throwable $e) {
    http_response_code(500);
    echo "Erro ao migrar: " . $e->getMessage();
}


