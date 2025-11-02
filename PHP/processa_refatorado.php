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
$dados = [
    'nome' => Request::post('nome', ''),
    'endereco' => Request::post('endereco', ''),
    'telefone' => Request::post('telefone', ''),
    'email' => Request::post('email', ''),
    'genero' => Request::post('genero', ''),
    'estado_civil' => Request::post('estado_civil', ''),
    'nascimento' => Request::post('nascimento', ''),
    'escolaridade' => Request::post('escolaridade', ''),
    'outros_cursos' => Request::post('outros_cursos', '')
];

// Validação usando Validator
$rules = [
    'nome' => ['label' => 'Nome completo', 'rules' => 'required|min:3'],
    'endereco' => ['label' => 'Endereço', 'rules' => 'required'],
    'telefone' => ['label' => 'Telefone', 'rules' => 'required'],
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

// Foto
if (!empty($_FILES['foto']['name'])) {
    $resultado = $files->upload($_FILES['foto'], 'curriculos', 'foto');
    if (!$resultado['status']) {
        $erros[] = 'Foto: ' . $resultado['message'];
    } else {
        $dados['foto'] = $resultado['path'];
    }
}

// Certificado
if (!empty($_FILES['certificado']['name'])) {
    $resultado = $files->upload($_FILES['certificado'], 'curriculos', 'certificado');
    if (!$resultado['status']) {
        $erros[] = 'Certificado: ' . $resultado['message'];
    } else {
        $dados['certificado'] = $resultado['path'];
    }
}

// Currículo
if (!empty($_FILES['curriculo']['name'])) {
    $resultado = $files->upload($_FILES['curriculo'], 'curriculos', 'curriculo');
    if (!$resultado['status']) {
        $erros[] = 'Currículo: ' . $resultado['message'];
    } else {
        $dados['curriculo'] = $resultado['path'];
    }
}

// Validar experiências
$empresas = Request::post('empresa', []);
$cargos = Request::post('cargo', []);
$atividades = Request::post('atividades', []);

if (!is_array($empresas) || !is_array($cargos) || !is_array($atividades)) {
    $erros[] = 'Estrutura de experiências inválida.';
} else {
    if (count($empresas) > 5) {
        $erros[] = 'Excedido o limite de experiências (máx 5).';
    }
    if (count($empresas) === 0) {
        $erros[] = 'Informe ao menos uma experiência.';
    }
    foreach ($empresas as $i => $empresa) {
        $empresa = limpar($empresa);
        $cargo = limpar($cargos[$i] ?? '');
        $atividade = limpar($atividades[$i] ?? '');
        if ($empresa === '' || $cargo === '' || $atividade === '') {
            $erros[] = 'Preencha Empresa, Cargo/Função e Atividades em todas as experiências.';
            break;
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

// Salvar no banco (exemplo - você precisa criar a tabela)
/*
$db = new Database();
try {
    $id = $db->table('curriculos')->insert([
        'nome' => $dados['nome'],
        'endereco' => $dados['endereco'],
        'telefone' => $dados['telefone'],
        'email' => $dados['email'],
        'genero' => $dados['genero'],
        'estado_civil' => $dados['estado_civil'],
        'nascimento' => $dados['nascimento'],
        'escolaridade' => $dados['escolaridade'],
        'outros_cursos' => $dados['outros_cursos'],
        'foto' => $dados['foto'] ?? null,
        'certificado' => $dados['certificado'] ?? null,
        'curriculo' => $dados['curriculo'] ?? null,
        'created_at' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    $erros[] = 'Erro ao salvar: ' . $e->getMessage();
}
*/

// Sucesso
$title = 'Cadastro recebido com sucesso!';
$messages = [
    'Seus dados foram validados. Em breve entraremos em contato.',
    'Resumo: ' . htmlspecialchars($dados['nome']) . ' - ' . htmlspecialchars($dados['email'])
];
$type = 'success';
$links = ['../HTML/index.html' => 'Voltar ao início'];
include __DIR__ . '/partials/layout.php';

