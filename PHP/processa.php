<?php
/**
 * Processa envio do formulário de currículo
 * Versão refatorada usando bibliotecas do AtomPHP
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    http_response_code(405);
    echo 'Método não permitido';
    exit;
}

$erros = [];

// Coletar dados usando Request
$rua = Request::post('rua', '');
$numero = Request::post('numero', '');
$bairro = Request::post('bairro', '');
$cidade = Request::post('cidade', '');
$cep = Request::post('cep', '');

// Combinar campos de endereço em um único campo
$enderecoCompleto = trim($rua . ($numero ? ', ' . $numero : '') . ($bairro ? ' - ' . $bairro : '') . ($cidade ? ', ' . $cidade : '') . ($cep ? ' - CEP: ' . $cep : ''));

$dados = [
    'nome' => Request::post('nome', ''),
    'endereco' => $enderecoCompleto ?: Request::post('endereco', ''), // Fallback para campo único se existir
    'telefone' => Request::post('telefone', ''),
    'email' => Request::post('email', ''),
    'genero' => Request::post('genero', ''),
    'estado_civil' => Request::post('estado_civil', ''),
    'nascimento' => Request::post('nascimento', ''),
    'escolaridade' => Request::post('escolaridade', ''),
    'outros_cursos' => Request::post('outros_cursos', '')
];

// Validação usando Validator
// Validar campos de endereço separados
if (empty($rua) || empty($bairro) || empty($cidade)) {
    $erros[] = 'Endereço incompleto. Preencha Rua, Bairro e Cidade.';
}

// Se endereço completo foi construído, validar
if (empty($enderecoCompleto) && empty(Request::post('endereco', ''))) {
    $erros[] = 'Endereço é obrigatório.';
}

$rules = [
    'nome' => ['label' => 'Nome completo', 'rules' => 'required|min:3'],
    'endereco' => ['label' => 'Endereço', 'rules' => 'required'],
    'telefone' => ['label' => 'Telefone', 'rules' => 'required|telefone'],
    'email' => ['label' => 'E-mail', 'rules' => 'required|email'],
    'genero' => ['label' => 'Gênero', 'rules' => 'required'],
    'nascimento' => ['label' => 'Data de nascimento', 'rules' => 'required|date'],
    'escolaridade' => ['label' => 'Grau de escolaridade', 'rules' => 'required']
];

// Validar dados básicos
if (!Validator::make($dados, $rules)) {
    $erros = array_merge($erros, array_values(Validator::getErrors()));
}

// Validar arquivos
$files = new Files();

// Foto (1MB, jpg/jpeg/png/gif)
if (!empty($_FILES['foto']['name'])) {
    $filesFoto = new Files(null, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'], 1);
    $resultado = $filesFoto->upload($_FILES['foto'], 'curriculos', 'foto');
    if (!$resultado['status']) {
        $erros[] = 'Foto: ' . $resultado['message'];
    } else {
        $dados['foto'] = $resultado['path'];
    }
}

// Certificado (5MB, pdf/jpg/jpeg/png)
if (!empty($_FILES['certificado']['name'])) {
    $filesCert = new Files(null, [
        'application/pdf',
        'image/jpeg', 
        'image/jpg', 
        'image/png'
    ], 5);
    $resultado = $filesCert->upload($_FILES['certificado'], 'curriculos', 'certificado');
    if (!$resultado['status']) {
        $erros[] = 'Certificado: ' . $resultado['message'];
    } else {
        $dados['certificado'] = $resultado['path'];
    }
}

// Currículo (10MB, pdf/doc/docx/txt)
if (!empty($_FILES['curriculo']['name'])) {
    $filesCurr = new Files(null, [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ], 10);
    $resultado = $filesCurr->upload($_FILES['curriculo'], 'curriculos', 'curriculo');
    if (!$resultado['status']) {
        $erros[] = 'Currículo: ' . $resultado['message'];
    } else {
        $dados['curriculo'] = $resultado['path'];
    }
}

// Validar experiências (opcional, mas se houver, validar)
$empresas = Request::post('empresa', []);
$cargos = Request::post('cargo', []);
$atividades = Request::post('atividades', []);

// Se não houver arrays, inicializar como arrays vazios
if (!is_array($empresas)) $empresas = [];
if (!is_array($cargos)) $cargos = [];
if (!is_array($atividades)) $atividades = [];

// Validar se houver experiências
if (count($empresas) > 0) {
    if (count($empresas) > 5) {
        $erros[] = 'Excedido o limite de experiências (máx 5).';
    }
    
    // Validar que cada experiência tem todos os campos preenchidos
    foreach ($empresas as $i => $empresa) {
        $empresa = limpar($empresa);
        $cargo = limpar($cargos[$i] ?? '');
        $atividade = limpar($atividades[$i] ?? '');
        
        // Se pelo menos um campo estiver preenchido, todos devem estar
        if (!empty($empresa) || !empty($cargo) || !empty($atividade)) {
            if (empty($empresa) || empty($cargo) || empty($atividade)) {
                $erros[] = 'Preencha Empresa, Cargo/Função e Atividades em todas as experiências.';
                break;
            }
        }
    }
}

// Se houver erros, exibir
if (!empty($erros)) {
    $title = 'Erros no cadastro de currículo';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/Cadastro_de_currículo.html' => 'Voltar'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Salvar no banco
$db = new Database();
try {
    // Processar experiências
    $experiencias = [];
    if (count($empresas) > 0) {
        for ($i = 0; $i < count($empresas); $i++) {
            $empresa = limpar($empresas[$i] ?? '');
            $cargo = limpar($cargos[$i] ?? '');
            $atividade = limpar($atividades[$i] ?? '');
            
            // Só adicionar se todos os campos estiverem preenchidos
            if (!empty($empresa) && !empty($cargo) && !empty($atividade)) {
                $experiencias[] = [
                    'empresa' => $empresa,
                    'cargo' => $cargo,
                    'atividades' => $atividade
                ];
            }
        }
    }
    
    // Verificar se já existe currículo com esse email (atualizar ou inserir)
    $curriculoExistente = $db->table('curriculos')
        ->where('email', $dados['email'])
        ->first();
    
    $dadosInsert = [
        'nome' => $dados['nome'],
        'endereco' => $dados['endereco'],
        'telefone' => $dados['telefone'],
        'email' => $dados['email'],
        'genero' => $dados['genero'],
        'estado_civil' => $dados['estado_civil'],
        'nascimento' => $dados['nascimento'],
        'escolaridade' => $dados['escolaridade'],
        'outros_cursos' => $dados['outros_cursos'] ?: null,
        'foto' => $dados['foto'] ?? null,
        'certificado' => $dados['certificado'] ?? null,
        'curriculo' => $dados['curriculo'] ?? null,
        'experiencias' => count($experiencias) > 0 ? json_encode($experiencias) : null,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    if ($curriculoExistente) {
        // Atualizar currículo existente
        $resultado = $db->table('curriculos')
            ->where('email', $dados['email'])
            ->update($dadosInsert);
        $id = $curriculoExistente['id'];
    } else {
        // Inserir novo currículo
        $id = $db->table('curriculos')->insert($dadosInsert);
        if (!$id) {
            throw new \Exception('Erro ao inserir currículo no banco de dados');
        }
    }
} catch (\Exception $e) {
    $erros[] = 'Erro ao salvar: ' . $e->getMessage();
    $title = 'Erro no cadastro';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/index.html' => 'Voltar'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Sucesso
$title = 'Cadastro recebido com sucesso!';
$messages = [
    'Seus dados foram validados. Em breve entraremos em contato.',
    'Resumo: ' . htmlspecialchars($dados['nome']) . ' - ' . htmlspecialchars($dados['email'])
];
$type = 'success';
$links = ['../HTML/index.html' => 'Voltar ao início'];
include __DIR__ . '/partials/layout.php';
