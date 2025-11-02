<?php
session_start();

// Configurações de erro (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'descubra_muriae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para validar dados da vaga
function validarVaga($dados) {
    $erros = [];
    
    if (empty($dados['titulo'])) {
        $erros[] = 'Título da vaga é obrigatório';
    }
    
    if (empty($dados['categoria'])) {
        $erros[] = 'Categoria é obrigatória';
    }
    
    if (empty($dados['dataLimite'])) {
        $erros[] = 'Data limite é obrigatória';
    } else {
        $dataLimite = new DateTime($dados['dataLimite']);
        $hoje = new DateTime();
        if ($dataLimite <= $hoje) {
            $erros[] = 'Data limite deve ser futura';
        }
    }
    
    if (empty($dados['descricao'])) {
        $erros[] = 'Descrição é obrigatória';
    }
    
    if (strlen($dados['descricao']) < 50) {
        $erros[] = 'Descrição deve ter pelo menos 50 caracteres';
    }
    
    return $erros;
}

// Função para sanitizar dados
function sanitizarDados($dados) {
    return array_map(function($valor) {
        return trim(strip_tags($valor));
    }, $dados);
}

// Processar requisições
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    switch ($acao) {
        case 'criar_vaga':
            $dados = sanitizarDados($_POST);
            $erros = validarVaga($dados);
            
            if (empty($erros)) {
                try {
                    $sql = "INSERT INTO vagas (
                        titulo, categoria, salario, tipo_contrato, data_publicacao, 
                        data_limite, status, descricao, requisitos, beneficios, 
                        empresa_id, ativo
                    ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, 1)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $dados['titulo'],
                        $dados['categoria'],
                        $dados['salario'],
                        $dados['tipoContrato'],
                        $dados['dataLimite'],
                        $dados['status'],
                        $dados['descricao'],
                        $dados['requisitos'],
                        $dados['beneficios'],
                        $_SESSION['empresa_id'] ?? 1 // ID da empresa logada
                    ]);
                    
                    $resposta = [
                        'sucesso' => true,
                        'mensagem' => 'Vaga criada com sucesso!',
                        'vaga_id' => $pdo->lastInsertId()
                    ];
                    
                } catch (PDOException $e) {
                    $resposta = [
                        'sucesso' => false,
                        'mensagem' => 'Erro ao criar vaga: ' . $e->getMessage()
                    ];
                }
            } else {
                $resposta = [
                    'sucesso' => false,
                    'mensagem' => 'Erro na validação dos dados',
                    'erros' => $erros
                ];
            }
            
            echo json_encode($resposta);
            break;
            
        case 'editar_vaga':
            $dados = sanitizarDados($_POST);
            $erros = validarVaga($dados);
            
            if (empty($erros)) {
                try {
                    $sql = "UPDATE vagas SET 
                        titulo = ?, categoria = ?, salario = ?, tipo_contrato = ?, 
                        data_limite = ?, status = ?, descricao = ?, requisitos = ?, 
                        beneficios = ?, data_atualizacao = NOW()
                        WHERE id = ? AND empresa_id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $dados['titulo'],
                        $dados['categoria'],
                        $dados['salario'],
                        $dados['tipoContrato'],
                        $dados['dataLimite'],
                        $dados['status'],
                        $dados['descricao'],
                        $dados['requisitos'],
                        $dados['beneficios'],
                        $dados['id'],
                        $_SESSION['empresa_id'] ?? 1
                    ]);
                    
                    $resposta = [
                        'sucesso' => true,
                        'mensagem' => 'Vaga atualizada com sucesso!'
                    ];
                    
                } catch (PDOException $e) {
                    $resposta = [
                        'sucesso' => false,
                        'mensagem' => 'Erro ao atualizar vaga: ' . $e->getMessage()
                    ];
                }
            } else {
                $resposta = [
                    'sucesso' => false,
                    'mensagem' => 'Erro na validação dos dados',
                    'erros' => $erros
                ];
            }
            
            echo json_encode($resposta);
            break;
            
        case 'excluir_vaga':
            $vagaId = $_POST['vaga_id'] ?? '';
            
            if (empty($vagaId)) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'ID da vaga é obrigatório'
                ]);
                break;
            }
            
            try {
                // Verificar se a vaga pertence à empresa
                $sql = "SELECT id FROM vagas WHERE id = ? AND empresa_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$vagaId, $_SESSION['empresa_id'] ?? 1]);
                
                if ($stmt->fetch()) {
                    // Excluir candidaturas relacionadas
                    $sql = "DELETE FROM candidaturas WHERE vaga_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$vagaId]);
                    
                    // Excluir vaga
                    $sql = "DELETE FROM vagas WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$vagaId]);
                    
                    $resposta = [
                        'sucesso' => true,
                        'mensagem' => 'Vaga excluída com sucesso!'
                    ];
                } else {
                    $resposta = [
                        'sucesso' => false,
                        'mensagem' => 'Vaga não encontrada ou não pertence à empresa'
                    ];
                }
                
            } catch (PDOException $e) {
                $resposta = [
                    'sucesso' => false,
                    'mensagem' => 'Erro ao excluir vaga: ' . $e->getMessage()
                ];
            }
            
            echo json_encode($resposta);
            break;
            
        case 'alterar_status':
            $vagaId = $_POST['vaga_id'] ?? '';
            $novoStatus = $_POST['status'] ?? '';
            
            if (empty($vagaId) || empty($novoStatus)) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'ID da vaga e status são obrigatórios'
                ]);
                break;
            }
            
            $statusValidos = ['Aberta', 'Pausada', 'Fechada'];
            if (!in_array($novoStatus, $statusValidos)) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Status inválido'
                ]);
                break;
            }
            
            try {
                $sql = "UPDATE vagas SET status = ?, data_atualizacao = NOW() 
                        WHERE id = ? AND empresa_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$novoStatus, $vagaId, $_SESSION['empresa_id'] ?? 1]);
                
                $resposta = [
                    'sucesso' => true,
                    'mensagem' => 'Status da vaga alterado com sucesso!'
                ];
                
            } catch (PDOException $e) {
                $resposta = [
                    'sucesso' => false,
                    'mensagem' => 'Erro ao alterar status: ' . $e->getMessage()
                ];
            }
            
            echo json_encode($resposta);
            break;
            
        case 'listar_vagas':
            try {
                $sql = "SELECT v.*, 
                        COUNT(c.id) as total_candidatos
                        FROM vagas v 
                        LEFT JOIN candidaturas c ON v.id = c.vaga_id 
                        WHERE v.empresa_id = ? AND v.ativo = 1
                        GROUP BY v.id 
                        ORDER BY v.data_publicacao DESC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['empresa_id'] ?? 1]);
                $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'sucesso' => true,
                    'vagas' => $vagas
                ]);
                
            } catch (PDOException $e) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Erro ao listar vagas: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'listar_candidatos':
            $vagaId = $_POST['vaga_id'] ?? '';
            
            if (empty($vagaId)) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'ID da vaga é obrigatório'
                ]);
                break;
            }
            
            try {
                $sql = "SELECT c.*, p.nome, p.email, p.telefone, p.curriculo
                        FROM candidaturas c
                        JOIN pessoas p ON c.pessoa_id = p.id
                        WHERE c.vaga_id = ? AND c.vaga_id IN (
                            SELECT id FROM vagas WHERE empresa_id = ?
                        )
                        ORDER BY c.data_candidatura DESC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$vagaId, $_SESSION['empresa_id'] ?? 1]);
                $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'sucesso' => true,
                    'candidatos' => $candidatos
                ]);
                
            } catch (PDOException $e) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Erro ao listar candidatos: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'avaliar_candidato':
            $candidatoId = $_POST['candidato_id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            if (empty($candidatoId) || empty($status)) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'ID do candidato e status são obrigatórios'
                ]);
                break;
            }
            
            $statusValidos = ['Aprovado', 'Reprovado', 'Pendente'];
            if (!in_array($status, $statusValidos)) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Status inválido'
                ]);
                break;
            }
            
            try {
                $sql = "UPDATE candidaturas SET status = ?, data_avaliacao = NOW() 
                        WHERE id = ? AND vaga_id IN (
                            SELECT id FROM vagas WHERE empresa_id = ?
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$status, $candidatoId, $_SESSION['empresa_id'] ?? 1]);
                
                $resposta = [
                    'sucesso' => true,
                    'mensagem' => 'Candidato avaliado com sucesso!'
                ];
                
            } catch (PDOException $e) {
                $resposta = [
                    'sucesso' => false,
                    'mensagem' => 'Erro ao avaliar candidato: ' . $e->getMessage()
                ];
            }
            
            echo json_encode($resposta);
            break;
            
        default:
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Ação não reconhecida'
            ]);
            break;
    }
} else {
    // Método não permitido
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
}
?>
