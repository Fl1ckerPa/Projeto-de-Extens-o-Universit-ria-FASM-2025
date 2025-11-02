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

// Função para validar CNPJ
function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
    
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
}

// Função para validar e-mail
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para validar telefone
function validarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    return strlen($telefone) >= 10 && strlen($telefone) <= 11;
}

// Função para validar URL
function validarURL($url) {
    if (empty($url)) return true; // URL é opcional
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Função para upload de arquivo
function uploadArquivo($arquivo, $pasta, $tiposPermitidos, $tamanhoMaximo) {
    if (!isset($arquivo) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'mensagem' => 'Nenhum arquivo enviado'];
    }
    
    $nomeArquivo = $arquivo['name'];
    $tamanhoArquivo = $arquivo['size'];
    $tipoArquivo = $arquivo['type'];
    $caminhoTemporario = $arquivo['tmp_name'];
    
    // Verificar tamanho
    if ($tamanhoArquivo > $tamanhoMaximo) {
        return ['sucesso' => false, 'mensagem' => 'Arquivo muito grande'];
    }
    
    // Verificar tipo
    $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
    if (!in_array($extensao, $tiposPermitidos)) {
        return ['sucesso' => false, 'mensagem' => 'Tipo de arquivo não permitido'];
    }
    
    // Gerar nome único
    $nomeUnico = uniqid() . '_' . time() . '.' . $extensao;
    $caminhoDestino = $pasta . '/' . $nomeUnico;
    
    // Criar pasta se não existir
    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }
    
    // Mover arquivo
    if (move_uploaded_file($caminhoTemporario, $caminhoDestino)) {
        return ['sucesso' => true, 'caminho' => $caminhoDestino, 'nome' => $nomeUnico];
    } else {
        return ['sucesso' => false, 'mensagem' => 'Erro ao salvar arquivo'];
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erros = [];
    $dados = [];
    
    // Validar e sanitizar dados
    $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
    if (empty($cnpj) || !validarCNPJ($cnpj)) {
        $erros[] = 'CNPJ inválido';
    } else {
        $dados['cnpj'] = $cnpj;
    }
    
    $nomeSocial = trim($_POST['nome_social'] ?? '');
    if (empty($nomeSocial)) {
        $erros[] = 'Nome social é obrigatório';
    } else {
        $dados['nome_social'] = $nomeSocial;
    }
    
    $segmento = $_POST['segmento'] ?? '';
    if (empty($segmento)) {
        $erros[] = 'Segmento é obrigatório';
    } else {
        $dados['segmento'] = $segmento;
    }
    
    $endereco = trim($_POST['endereco'] ?? '');
    if (empty($endereco)) {
        $erros[] = 'Endereço é obrigatório';
    } else {
        $dados['endereco'] = $endereco;
    }
    
    $cidade = trim($_POST['cidade'] ?? '');
    if (empty($cidade)) {
        $erros[] = 'Cidade é obrigatória';
    } else {
        $dados['cidade'] = $cidade;
    }
    
    $estado = $_POST['estado'] ?? '';
    if (empty($estado)) {
        $erros[] = 'Estado é obrigatório';
    } else {
        $dados['estado'] = $estado;
    }
    
    $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');
    $dados['cep'] = $cep;
    
    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !validarEmail($email)) {
        $erros[] = 'E-mail inválido';
    } else {
        $dados['email'] = $email;
    }
    
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
    if (empty($telefone) || !validarTelefone($telefone)) {
        $erros[] = 'Telefone inválido';
    } else {
        $dados['telefone'] = $telefone;
    }
    
    $site = trim($_POST['site'] ?? '');
    if (!empty($site) && !validarURL($site)) {
        $erros[] = 'Site inválido';
    } else {
        $dados['site'] = $site;
    }
    
    $linkedin = trim($_POST['linkedin'] ?? '');
    if (!empty($linkedin) && !validarURL($linkedin)) {
        $erros[] = 'LinkedIn inválido';
    } else {
        $dados['linkedin'] = $linkedin;
    }
    
    $sobre = trim($_POST['sobre'] ?? '');
    if (empty($sobre) || strlen($sobre) < 50) {
        $erros[] = 'Descrição deve ter pelo menos 50 caracteres';
    } else {
        $dados['sobre'] = $sobre;
    }
    
    $funcionarios = $_POST['funcionarios'] ?? '';
    $dados['funcionarios'] = $funcionarios;
    
    $fundacao = $_POST['fundacao'] ?? '';
    $dados['fundacao'] = $fundacao;
    
    // Processar upload da logo
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadArquivo(
            $_FILES['logo'],
            '../uploads/logos',
            ['jpg', 'jpeg', 'png', 'gif'],
            2 * 1024 * 1024 // 2MB
        );
        
        if (!$uploadResult['sucesso']) {
            $erros[] = $uploadResult['mensagem'];
        } else {
            $logoPath = $uploadResult['caminho'];
        }
    }
    
    // Se não há erros, inserir no banco
    if (empty($erros)) {
        try {
            // Verificar se CNPJ já existe
            $stmt = $pdo->prepare("SELECT id FROM empresas WHERE cnpj = ?");
            $stmt->execute([$cnpj]);
            if ($stmt->fetch()) {
                $erros[] = 'CNPJ já cadastrado';
            } else {
                // Inserir empresa
                $sql = "INSERT INTO empresas (
                    cnpj, nome_social, segmento, endereco, cidade, estado, cep,
                    email, telefone, site, linkedin, sobre, funcionarios, fundacao, logo,
                    data_cadastro, ativo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $dados['cnpj'],
                    $dados['nome_social'],
                    $dados['segmento'],
                    $dados['endereco'],
                    $dados['cidade'],
                    $dados['estado'],
                    $dados['cep'],
                    $dados['email'],
                    $dados['telefone'],
                    $dados['site'],
                    $dados['linkedin'],
                    $dados['sobre'],
                    $dados['funcionarios'],
                    $dados['fundacao'],
                    $logoPath
                ]);
                
                $empresaId = $pdo->lastInsertId();
                
                // Resposta de sucesso
                $resposta = [
                    'sucesso' => true,
                    'mensagem' => 'Empresa cadastrada com sucesso!',
                    'empresa_id' => $empresaId
                ];
                
                echo json_encode($resposta);
                exit;
            }
        } catch (PDOException $e) {
            $erros[] = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
        }
    }
    
    // Se há erros, retornar
    if (!empty($erros)) {
        $resposta = [
            'sucesso' => false,
            'mensagem' => 'Erro na validação dos dados',
            'erros' => $erros
        ];
        
        echo json_encode($resposta);
        exit;
    }
} else {
    // Método não permitido
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
    exit;
}
?>
