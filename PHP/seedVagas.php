<?php
require_once __DIR__ . '/../lib/bootstrap.php';

// Inicializa contadores de resumo
$resumo = [
    'criados' => 0,
    'pulados' => 0,
    'erros' => 0,
    'tabelas_ausentes' => []
];

// Função para exibir mensagens formatadas
function exibir($mensagem, $tipo = 'info') {
    $icones = [
        'info' => 'ℹ️',
        'sucesso' => '✓',
        'erro' => '✗',
        'aviso' => '⚠️',
        'pulado' => '⊘'
    ];
    $icone = $icones[$tipo] ?? '•';
    echo "{$icone} {$mensagem}\n";
}

// Função para verificar se uma tabela existe
function tabelaExiste($pdo, $nomeTabela) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$nomeTabela]);
        return $stmt->rowCount() > 0;
    } catch (\PDOException $e) {
        return false;
    }
}

// Função para obter colunas de uma tabela
function obterColunas($pdo, $nomeTabela) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$nomeTabela}`");
        $colunas = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $colunas[] = $row['Field'];
        }
        return $colunas;
    } catch (\PDOException $e) {
        return [];
    }
}

// Função para detectar coluna FK de empresa na tabela vagas
function detectarColunaEmpresa($pdo, $nomeTabela = 'vagas') {
    $possiveisColunas = ['empresa_id', 'pj_id', 'usuario_pj_id', 'id_empresa', 'empresa_fk'];
    
    if (!tabelaExiste($pdo, $nomeTabela)) {
        return null;
    }
    
    $colunas = obterColunas($pdo, $nomeTabela);
    
    foreach ($possiveisColunas as $coluna) {
        if (in_array($coluna, $colunas)) {
            return $coluna;
        }
    }
    
    return null;
}

// Função para formatar CPF
function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

// Função para formatar CNPJ
function formatarCNPJ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
}

echo "\n";
echo "========================================\n";
echo "  SCRIPT DE SEED - DESCOBRA MURIÁE\n";
echo "========================================\n\n";

try {
    // ============================================
    // 1. VERIFICAÇÃO E CONFIGURAÇÃO DA CONEXÃO
    // ============================================
    
    exibir("Verificando conexão com o banco de dados...", 'info');
    
    // Verifica se $pdo já existe (definido em lib/config.php)
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        // Verifica se config.php foi carregado
        if (!defined('DB_HOST') || !defined('DB_DATABASE')) {
            throw new \Exception(
                "Erro: lib/config.php não foi carregado corretamente ou não define as constantes necessárias.\n" .
                "Verifique se o arquivo existe e contém as definições DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD.\n" .
                "Alternativamente, lib/config.php deve expor uma variável \$pdo que seja uma instância PDO."
            );
        }
        
        // Tenta criar conexão PDO usando a classe Database
        try {
            $db = new Database();
            $pdo = $db->connect();
        } catch (\Exception $e) {
            $mensagemErro = "Erro ao criar conexão PDO:\n";
            $mensagemErro .= "  - Mensagem: " . $e->getMessage() . "\n";
            $mensagemErro .= "  - Host: " . DB_HOST . ":" . DB_PORT . "\n";
            $mensagemErro .= "  - Banco: " . DB_DATABASE . "\n";
            $mensagemErro .= "  - Usuário: " . DB_USER . "\n\n";
            $mensagemErro .= "DIAGNÓSTICO:\n";
            $mensagemErro .= "  1. Verifique se o MySQL/MariaDB está rodando\n";
            $mensagemErro .= "  2. Confirme as credenciais em lib/config.php\n";
            $mensagemErro .= "  3. Verifique se o banco de dados existe\n";
            $mensagemErro .= "  4. Teste a conexão manualmente com: mysql -h " . DB_HOST . " -u " . DB_USER . " -p " . DB_DATABASE;
            
            throw new \Exception($mensagemErro);
        }
    } else {
        exibir("Variável \$pdo encontrada em lib/config.php", 'sucesso');
    }
    
    // Testa a conexão
    try {
        $pdo->query("SELECT 1");
        exibir("Conexão estabelecida com sucesso!", 'sucesso');
        exibir("Host: " . DB_HOST . ":" . DB_PORT, 'info');
        exibir("Banco: " . DB_DATABASE, 'info');
    } catch (\PDOException $e) {
        $mensagemErro = "Erro ao testar conexão PDO:\n";
        $mensagemErro .= "  - Mensagem: " . $e->getMessage() . "\n";
        $mensagemErro .= "  - Host: " . DB_HOST . ":" . DB_PORT . "\n";
        $mensagemErro .= "  - Banco: " . DB_DATABASE . "\n";
        $mensagemErro .= "  - Usuário: " . DB_USER . "\n\n";
        $mensagemErro .= "DIAGNÓSTICO:\n";
        $mensagemErro .= "  1. Verifique se o MySQL/MariaDB está rodando\n";
        $mensagemErro .= "  2. Confirme as credenciais em lib/config.php\n";
        $mensagemErro .= "  3. Verifique se o banco de dados existe\n";
        $mensagemErro .= "  4. Teste a conexão manualmente com: mysql -h " . DB_HOST . " -u " . DB_USER . " -p " . DB_DATABASE;
        
        throw new \Exception($mensagemErro);
    }
    
    // ============================================
    // 2. VERIFICAÇÃO DE TABELAS NECESSÁRIAS
    // ============================================
    
    echo "\n";
    exibir("Verificando existência das tabelas...", 'info');
    
    $tabelasNecessarias = [
        'usuarios_pf' => 'Pessoa Física',
        'usuarios_pj' => 'Pessoa Jurídica',
        'vagas' => 'Vagas',
        'administradores' => 'Administradores'
    ];
    
    $tabelasOpcionais = [
        'empresas' => 'Empresas'
    ];
    
    $tabelasExistentes = [];
    $tabelasAusentes = [];
    
    foreach ($tabelasNecessarias as $tabela => $descricao) {
        if (tabelaExiste($pdo, $tabela)) {
            $tabelasExistentes[$tabela] = true;
            exibir("Tabela '{$tabela}' encontrada", 'sucesso');
        } else {
            $tabelasAusentes[$tabela] = $descricao;
            $resumo['tabelas_ausentes'][] = $tabela;
            exibir("Tabela '{$tabela}' NÃO encontrada", 'erro');
        }
    }
    
    foreach ($tabelasOpcionais as $tabela => $descricao) {
        if (tabelaExiste($pdo, $tabela)) {
            $tabelasExistentes[$tabela] = true;
            exibir("Tabela '{$tabela}' encontrada (opcional)", 'sucesso');
        } else {
            exibir("Tabela '{$tabela}' não encontrada (opcional, será ignorada)", 'aviso');
        }
    }
    
    if (!empty($tabelasAusentes)) {
        echo "\n";
        exibir("ATENÇÃO: Algumas tabelas necessárias não foram encontradas!", 'erro');
        exibir("Execute as migrations primeiro: http://localhost:8000/PHP/migrate.php", 'aviso');
        echo "\n";
    }
    
    // ============================================
    // 3. CRIAÇÃO DE REGISTROS DE TESTE
    // ============================================
    
    echo "\n";
    echo "========================================\n";
    echo "  CRIANDO REGISTROS DE TESTE\n";
    echo "========================================\n\n";
    
    // Dados de teste
    $cpfTeste = '11144477735';
    $cpfFormatado = formatarCPF($cpfTeste);
    $cnpjTeste = '11222333000181';
    $cnpjFormatado = formatarCNPJ($cnpjTeste);
    $senhaTeste = 'Teste@123';
    $senhaAdmin = 'Admin@123';
    $emailAdmin = 'admin@descubramuriae.local';
    
    // Hash das senhas
    $senhaHash = password_hash($senhaTeste, PASSWORD_DEFAULT);
    $senhaAdminHash = password_hash($senhaAdmin, PASSWORD_DEFAULT);
    
    // --------------------------------------------
    // 3.1. PESSOA FÍSICA (usuarios_pf)
    // --------------------------------------------
    
    if (isset($tabelasExistentes['usuarios_pf'])) {
        exibir("Criando registro de Pessoa Física...", 'info');
        
        try {
            // Verifica se já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_pf WHERE cpf = ?");
            $stmt->execute([$cpfTeste]);
            $existe = $stmt->fetchColumn() > 0;
            
            if ($existe) {
                exibir("Pessoa Física com CPF {$cpfFormatado} já existe, pulando...", 'pulado');
                $resumo['pulados']++;
            } else {
                // Detecta colunas disponíveis
                $colunas = obterColunas($pdo, 'usuarios_pf');
                
                // Monta query dinamicamente
                $campos = [];
                $valores = [];
                $placeholders = [];
                
                if (in_array('cpf', $colunas)) {
                    $campos[] = 'cpf';
                    $valores[] = $cpfTeste;
                    $placeholders[] = '?';
                }
                
                if (in_array('senha', $colunas)) {
                    $campos[] = 'senha';
                    $valores[] = $senhaHash;
                    $placeholders[] = '?';
                } elseif (in_array('senha_hash', $colunas)) {
                    $campos[] = 'senha_hash';
                    $valores[] = $senhaHash;
                    $placeholders[] = '?';
                }
                
                // Campos opcionais comuns
                if (in_array('nome', $colunas)) {
                    $campos[] = 'nome';
                    $valores[] = 'Usuário PF Teste';
                    $placeholders[] = '?';
                }
                
                if (in_array('email', $colunas)) {
                    $campos[] = 'email';
                    $valores[] = 'pf.teste@descubramuriae.local';
                    $placeholders[] = '?';
                }
                
                if (in_array('ativo', $colunas)) {
                    $campos[] = 'ativo';
                    $valores[] = 1;
                    $placeholders[] = '?';
                }
                
                if (in_array('created_at', $colunas) || in_array('data_cadastro', $colunas)) {
                    $colunaData = in_array('created_at', $colunas) ? 'created_at' : 'data_cadastro';
                    $campos[] = $colunaData;
                    $valores[] = date('Y-m-d H:i:s');
                    $placeholders[] = '?';
                }
                
                if (!empty($campos)) {
                    $sql = "INSERT INTO usuarios_pf (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($valores);
                    
                    exibir("Pessoa Física criada com sucesso! CPF: {$cpfFormatado}", 'sucesso');
                    $resumo['criados']++;
                } else {
                    throw new \Exception("Não foi possível determinar quais colunas inserir na tabela usuarios_pf");
                }
            }
        } catch (\PDOException $e) {
            exibir("Erro ao criar Pessoa Física: " . $e->getMessage(), 'erro');
            $resumo['erros']++;
        }
    } else {
        exibir("Tabela usuarios_pf não encontrada, pulando criação de PF...", 'aviso');
    }
    
    // --------------------------------------------
    // 3.2. PESSOA JURÍDICA (usuarios_pj)
    // --------------------------------------------
    
    if (isset($tabelasExistentes['usuarios_pj'])) {
        exibir("Criando registro de Pessoa Jurídica...", 'info');
        
        try {
            // Verifica se já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_pj WHERE cnpj = ?");
            $stmt->execute([$cnpjTeste]);
            $existe = $stmt->fetchColumn() > 0;
            
            if ($existe) {
                exibir("Pessoa Jurídica com CNPJ {$cnpjFormatado} já existe, pulando...", 'pulado');
                $resumo['pulados']++;
            } else {
                // Detecta colunas disponíveis
                $colunas = obterColunas($pdo, 'usuarios_pj');
                
                // Monta query dinamicamente
                $campos = [];
                $valores = [];
                $placeholders = [];
                
                if (in_array('cnpj', $colunas)) {
                    $campos[] = 'cnpj';
                    $valores[] = $cnpjTeste;
                    $placeholders[] = '?';
                }
                
                if (in_array('senha', $colunas)) {
                    $campos[] = 'senha';
                    $valores[] = $senhaHash;
                    $placeholders[] = '?';
                } elseif (in_array('senha_hash', $colunas)) {
                    $campos[] = 'senha_hash';
                    $valores[] = $senhaHash;
                    $placeholders[] = '?';
                }
                
                // Campos opcionais comuns
                if (in_array('nome', $colunas) || in_array('razao_social', $colunas)) {
                    $colunaNome = in_array('razao_social', $colunas) ? 'razao_social' : 'nome';
                    $campos[] = $colunaNome;
                    $valores[] = 'Empresa PJ Teste';
                    $placeholders[] = '?';
                }
                
                if (in_array('email', $colunas)) {
                    $campos[] = 'email';
                    $valores[] = 'pj.teste@descubramuriae.local';
                    $placeholders[] = '?';
                }
                
                if (in_array('ativo', $colunas)) {
                    $campos[] = 'ativo';
                    $valores[] = 1;
                    $placeholders[] = '?';
                }
                
                if (in_array('created_at', $colunas) || in_array('data_cadastro', $colunas)) {
                    $colunaData = in_array('created_at', $colunas) ? 'created_at' : 'data_cadastro';
                    $campos[] = $colunaData;
                    $valores[] = date('Y-m-d H:i:s');
                    $placeholders[] = '?';
                }
                
                if (!empty($campos)) {
                    $sql = "INSERT INTO usuarios_pj (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($valores);
                    
                    exibir("Pessoa Jurídica criada com sucesso! CNPJ: {$cnpjFormatado}", 'sucesso');
                    $resumo['criados']++;
                } else {
                    throw new \Exception("Não foi possível determinar quais colunas inserir na tabela usuarios_pj");
                }
            }
        } catch (\PDOException $e) {
            exibir("Erro ao criar Pessoa Jurídica: " . $e->getMessage(), 'erro');
            $resumo['erros']++;
        }
    } else {
        exibir("Tabela usuarios_pj não encontrada, pulando criação de PJ...", 'aviso');
    }
    
    // --------------------------------------------
    // 3.3. EMPRESA (empresas) - Opcional
    // --------------------------------------------
    
    $empresaId = null;
    
    if (isset($tabelasExistentes['empresas'])) {
        exibir("Criando registro de Empresa...", 'info');
        
        try {
            // Verifica se já existe
            $stmt = $pdo->prepare("SELECT id, empresa_id FROM empresas WHERE cnpj = ? LIMIT 1");
            $stmt->execute([$cnpjTeste]);
            $empresaExistente = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($empresaExistente) {
                $empresaId = $empresaExistente['id'] ?? $empresaExistente['empresa_id'] ?? null;
                exibir("Empresa com CNPJ {$cnpjFormatado} já existe (ID: {$empresaId}), usando existente...", 'pulado');
                $resumo['pulados']++;
            } else {
                // Detecta colunas disponíveis
                $colunas = obterColunas($pdo, 'empresas');
                
                // Monta query dinamicamente
                $campos = [];
                $valores = [];
                $placeholders = [];
                
                if (in_array('cnpj', $colunas)) {
                    $campos[] = 'cnpj';
                    $valores[] = $cnpjTeste;
                    $placeholders[] = '?';
                }
                
                if (in_array('nome', $colunas) || in_array('razao_social', $colunas) || in_array('nome_social', $colunas)) {
                    $colunaNome = in_array('nome_social', $colunas) ? 'nome_social' : 
                                  (in_array('razao_social', $colunas) ? 'razao_social' : 'nome');
                    $campos[] = $colunaNome;
                    $valores[] = 'Empresa Teste Ltda';
                    $placeholders[] = '?';
                }
                
                if (in_array('email', $colunas)) {
                    $campos[] = 'email';
                    $valores[] = 'empresa.teste@descubramuriae.local';
                    $placeholders[] = '?';
                }
                
                if (in_array('ativo', $colunas)) {
                    $campos[] = 'ativo';
                    $valores[] = 1;
                    $placeholders[] = '?';
                }
                
                if (in_array('created_at', $colunas) || in_array('data_cadastro', $colunas)) {
                    $colunaData = in_array('created_at', $colunas) ? 'created_at' : 'data_cadastro';
                    $campos[] = $colunaData;
                    $valores[] = date('Y-m-d H:i:s');
                    $placeholders[] = '?';
                }
                
                if (!empty($campos)) {
                    $sql = "INSERT INTO empresas (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($valores);
                    
                    // Obtém o ID inserido
                    $idCol = in_array('id', $colunas) ? 'id' : (in_array('empresa_id', $colunas) ? 'empresa_id' : null);
                    if ($idCol) {
                        $empresaId = $pdo->lastInsertId();
                    } else {
                        // Tenta buscar pelo CNPJ
                        $stmt = $pdo->prepare("SELECT id, empresa_id FROM empresas WHERE cnpj = ? LIMIT 1");
                        $stmt->execute([$cnpjTeste]);
                        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                        $empresaId = $row['id'] ?? $row['empresa_id'] ?? null;
                    }
                    
                    exibir("Empresa criada com sucesso! CNPJ: {$cnpjFormatado} (ID: {$empresaId})", 'sucesso');
                    $resumo['criados']++;
                } else {
                    exibir("Não foi possível determinar quais colunas inserir na tabela empresas", 'aviso');
                }
            }
        } catch (\PDOException $e) {
            exibir("Erro ao criar Empresa: " . $e->getMessage(), 'erro');
            $resumo['erros']++;
        }
    } else {
        exibir("Tabela empresas não encontrada, será ignorada...", 'aviso');
    }
    
    // --------------------------------------------
    // 3.4. ADMINISTRADOR (administradores)
    // --------------------------------------------
    
    if (isset($tabelasExistentes['administradores'])) {
        exibir("Criando registro de Administrador...", 'info');
        
        try {
            // Verifica se já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM administradores WHERE email = ?");
            $stmt->execute([$emailAdmin]);
            $existe = $stmt->fetchColumn() > 0;
            
            if ($existe) {
                exibir("Administrador com email {$emailAdmin} já existe, pulando...", 'pulado');
                $resumo['pulados']++;
            } else {
                // Detecta colunas disponíveis
                $colunas = obterColunas($pdo, 'administradores');
                
                // Monta query dinamicamente
                $campos = [];
                $valores = [];
                $placeholders = [];
                
                if (in_array('email', $colunas)) {
                    $campos[] = 'email';
                    $valores[] = $emailAdmin;
                    $placeholders[] = '?';
                }
                
                if (in_array('senha', $colunas)) {
                    $campos[] = 'senha';
                    $valores[] = $senhaAdminHash;
                    $placeholders[] = '?';
                } elseif (in_array('senha_hash', $colunas)) {
                    $campos[] = 'senha_hash';
                    $valores[] = $senhaAdminHash;
                    $placeholders[] = '?';
                }
                
                // Campos opcionais comuns
                if (in_array('nome', $colunas)) {
                    $campos[] = 'nome';
                    $valores[] = 'Administrador Teste';
                    $placeholders[] = '?';
                }
                
                if (in_array('ativo', $colunas)) {
                    $campos[] = 'ativo';
                    $valores[] = 1;
                    $placeholders[] = '?';
                }
                
                if (in_array('created_at', $colunas) || in_array('data_cadastro', $colunas)) {
                    $colunaData = in_array('created_at', $colunas) ? 'created_at' : 'data_cadastro';
                    $campos[] = $colunaData;
                    $valores[] = date('Y-m-d H:i:s');
                    $placeholders[] = '?';
                }
                
                if (!empty($campos)) {
                    $sql = "INSERT INTO administradores (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($valores);
                    
                    exibir("Administrador criado com sucesso! Email: {$emailAdmin}", 'sucesso');
                    $resumo['criados']++;
                } else {
                    throw new \Exception("Não foi possível determinar quais colunas inserir na tabela administradores");
                }
            }
        } catch (\PDOException $e) {
            exibir("Erro ao criar Administrador: " . $e->getMessage(), 'erro');
            $resumo['erros']++;
        }
    } else {
        exibir("Tabela administradores não encontrada, pulando criação de Admin...", 'aviso');
    }
    
    // --------------------------------------------
    // 3.5. VAGAS (vagas)
    // --------------------------------------------
    
    if (isset($tabelasExistentes['vagas'])) {
        echo "\n";
        exibir("Criando vagas de exemplo...", 'info');
        
        // Detecta coluna FK de empresa
        $colunaEmpresa = detectarColunaEmpresa($pdo, 'vagas');
        
        if ($colunaEmpresa) {
            exibir("Coluna FK de empresa detectada: {$colunaEmpresa}", 'sucesso');
        } else {
            exibir("Nenhuma coluna FK de empresa encontrada, vagas serão criadas sem FK", 'aviso');
        }
        
        // Obtém colunas da tabela vagas
        $colunasVagas = obterColunas($pdo, 'vagas');
        
        // Define as 3 vagas de exemplo
        $vagasExemplo = [
            [
                'titulo' => 'Desenvolvedor Web Júnior',
                'descricao' => 'Vaga para desenvolvedor web júnior com conhecimento em PHP, HTML, CSS e JavaScript. Oportunidade de crescimento em empresa em expansão.',
                'requisitos' => [
                    'Conhecimento em PHP',
                    'HTML, CSS e JavaScript',
                    'Experiência com banco de dados MySQL',
                    'Boa comunicação e trabalho em equipe'
                ],
                'localidade' => 'Muriaé - MG',
                'tipo' => 'CLT',
                'categoria' => 'Tecnologia',
                'salario' => 'R$ 2.500,00 - R$ 3.500,00'
            ],
            [
                'titulo' => 'Analista de Marketing Digital',
                'descricao' => 'Buscamos profissional para gerenciar campanhas de marketing digital, redes sociais e estratégias de conteúdo. Experiência com Google Ads e Facebook Ads é desejável.',
                'requisitos' => [
                    'Experiência com marketing digital',
                    'Conhecimento em Google Ads e Facebook Ads',
                    'Criação de conteúdo para redes sociais',
                    'Análise de métricas e KPIs'
                ],
                'localidade' => 'Muriaé - MG',
                'tipo' => 'CLT',
                'categoria' => 'Marketing',
                'salario' => 'R$ 3.000,00 - R$ 4.500,00'
            ],
            [
                'titulo' => 'Estagiário de Design',
                'descricao' => 'Oportunidade de estágio para estudante de Design Gráfico ou áreas afins. Atuação em criação de materiais gráficos, identidade visual e suporte à equipe de marketing.',
                'requisitos' => [
                    'Cursando Design Gráfico ou áreas afins',
                    'Conhecimento em Adobe Photoshop, Illustrator e InDesign',
                    'Criatividade e atenção aos detalhes',
                    'Disponibilidade para estágio de 6 horas diárias'
                ],
                'localidade' => 'Muriaé - MG',
                'tipo' => 'Estágio',
                'categoria' => 'Design',
                'salario' => 'R$ 800,00 - R$ 1.200,00'
            ]
        ];
        
        foreach ($vagasExemplo as $vaga) {
            try {
                // Verifica se já existe (por título + localidade ou título + empresa)
                $whereConditions = ["titulo = ?"];
                $whereParams = [$vaga['titulo']];
                
                if (in_array('localidade', $colunasVagas)) {
                    $whereConditions[] = "localidade = ?";
                    $whereParams[] = $vaga['localidade'];
                } elseif ($colunaEmpresa && $empresaId) {
                    $whereConditions[] = "{$colunaEmpresa} = ?";
                    $whereParams[] = $empresaId;
                }
                
                $sqlCheck = "SELECT COUNT(*) FROM vagas WHERE " . implode(" AND ", $whereConditions);
                $stmt = $pdo->prepare($sqlCheck);
                $stmt->execute($whereParams);
                $existe = $stmt->fetchColumn() > 0;
                
                if ($existe) {
                    exibir("Vaga '{$vaga['titulo']}' já existe, pulando...", 'pulado');
                    $resumo['pulados']++;
                    continue;
                }
                
                // Monta query de inserção dinamicamente
                $campos = [];
                $valores = [];
                $placeholders = [];
                
                // Título (obrigatório)
                if (in_array('titulo', $colunasVagas)) {
                    $campos[] = 'titulo';
                    $valores[] = $vaga['titulo'];
                    $placeholders[] = '?';
                }
                
                // Descrição
                if (in_array('descricao', $colunasVagas)) {
                    $campos[] = 'descricao';
                    $valores[] = $vaga['descricao'];
                    $placeholders[] = '?';
                }
                
                // Requisitos (pode ser TEXT ou JSON)
                if (in_array('requisitos', $colunasVagas)) {
                    $campos[] = 'requisitos';
                    // Verifica se a coluna aceita JSON
                    $stmt = $pdo->query("SHOW COLUMNS FROM vagas WHERE Field = 'requisitos'");
                    $colInfo = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($colInfo && stripos($colInfo['Type'], 'json') !== false) {
                        $valores[] = json_encode($vaga['requisitos'], JSON_UNESCAPED_UNICODE);
                    } else {
                        $valores[] = implode("\n", $vaga['requisitos']);
                    }
                    $placeholders[] = '?';
                }
                
                // Localidade
                if (in_array('localidade', $colunasVagas)) {
                    $campos[] = 'localidade';
                    $valores[] = $vaga['localidade'];
                    $placeholders[] = '?';
                }
                
                // Tipo
                if (in_array('tipo', $colunasVagas)) {
                    $campos[] = 'tipo';
                    $valores[] = $vaga['tipo'];
                    $placeholders[] = '?';
                } elseif (in_array('tipo_contrato', $colunasVagas)) {
                    $campos[] = 'tipo_contrato';
                    $valores[] = $vaga['tipo'];
                    $placeholders[] = '?';
                }
                
                // Categoria
                if (in_array('categoria', $colunasVagas)) {
                    $campos[] = 'categoria';
                    $valores[] = $vaga['categoria'];
                    $placeholders[] = '?';
                }
                
                // Salário
                if (in_array('salario', $colunasVagas)) {
                    $campos[] = 'salario';
                    $valores[] = $vaga['salario'];
                    $placeholders[] = '?';
                }
                
                // FK de empresa (se detectada e disponível)
                if ($colunaEmpresa && in_array($colunaEmpresa, $colunasVagas) && $empresaId) {
                    $campos[] = $colunaEmpresa;
                    $valores[] = $empresaId;
                    $placeholders[] = '?';
                }
                
                // Campos de data
                $agora = date('Y-m-d H:i:s');
                if (in_array('created_at', $colunasVagas)) {
                    $campos[] = 'created_at';
                    $valores[] = $agora;
                    $placeholders[] = '?';
                } elseif (in_array('data_cadastro', $colunasVagas)) {
                    $campos[] = 'data_cadastro';
                    $valores[] = $agora;
                    $placeholders[] = '?';
                }
                
                if (in_array('updated_at', $colunasVagas)) {
                    $campos[] = 'updated_at';
                    $valores[] = $agora;
                    $placeholders[] = '?';
                } elseif (in_array('data_atualizacao', $colunasVagas)) {
                    $campos[] = 'data_atualizacao';
                    $valores[] = $agora;
                    $placeholders[] = '?';
                }
                
                // Status (se existir)
                if (in_array('status', $colunasVagas)) {
                    $campos[] = 'status';
                    $valores[] = 'Aberta';
                    $placeholders[] = '?';
                }
                
                // Ativo (se existir)
                if (in_array('ativo', $colunasVagas)) {
                    $campos[] = 'ativo';
                    $valores[] = 1;
                    $placeholders[] = '?';
                }
                
                if (!empty($campos)) {
                    $sql = "INSERT INTO vagas (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($valores);
                    
                    exibir("Vaga '{$vaga['titulo']}' criada com sucesso!", 'sucesso');
                    $resumo['criados']++;
                } else {
                    throw new \Exception("Não foi possível determinar quais colunas inserir na tabela vagas");
                }
                
            } catch (\PDOException $e) {
                exibir("Erro ao criar vaga '{$vaga['titulo']}': " . $e->getMessage(), 'erro');
                $resumo['erros']++;
            }
        }
    } else {
        exibir("Tabela vagas não encontrada, pulando criação de vagas...", 'aviso');
    }
    
    // ============================================
    // 4. RESUMO FINAL
    // ============================================
    
    echo "\n";
    echo "========================================\n";
    echo "  RESUMO DA EXECUÇÃO\n";
    echo "========================================\n\n";
    
    exibir("Registros criados: {$resumo['criados']}", 'sucesso');
    exibir("Registros pulados (já existiam): {$resumo['pulados']}", 'pulado');
    exibir("Erros encontrados: {$resumo['erros']}", $resumo['erros'] > 0 ? 'erro' : 'info');
    
    if (!empty($resumo['tabelas_ausentes'])) {
        echo "\n";
        exibir("Tabelas ausentes:", 'aviso');
        foreach ($resumo['tabelas_ausentes'] as $tabela) {
            echo "  - {$tabela}\n";
        }
        echo "\n";
        exibir("Execute as migrations: http://localhost:8000/PHP/migrate.php", 'aviso');
    }
    
    echo "\n";
    echo "========================================\n";
    echo "  CREDENCIAIS DE TESTE\n";
    echo "========================================\n\n";
    
    echo "PESSOA FÍSICA (PF):\n";
    echo "  CPF: {$cpfFormatado}\n";
    echo "  Senha: {$senhaTeste}\n\n";
    
    echo "PESSOA JURÍDICA (PJ):\n";
    echo "  CNPJ: {$cnpjFormatado}\n";
    echo "  Senha: {$senhaTeste}\n\n";
    
    echo "ADMINISTRADOR:\n";
    echo "  Email: {$emailAdmin}\n";
    echo "  Senha: {$senhaAdmin}\n\n";
    
    echo "========================================\n";
    echo "Script executado com sucesso!\n";
    echo "========================================\n\n";
    
} catch (\Exception $e) {
    echo "\n";
    echo "========================================\n";
    echo "  ERRO FATAL\n";
    echo "========================================\n\n";
    exibir($e->getMessage(), 'erro');
    echo "\n";
    http_response_code(500);
    exit(1);
}

