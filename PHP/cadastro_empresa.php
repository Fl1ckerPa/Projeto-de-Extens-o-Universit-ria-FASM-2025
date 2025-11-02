<?php
/**
 * Processa cadastro de empresa
 * Versão refatorada usando bibliotecas do AtomPHP
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$erros = [];
$dados = [];

// Validar CNPJ
$cnpj = preg_replace('/[^0-9]/', '', Request::post('cnpj', ''));
if (empty($cnpj) || !Helper::validarCNPJ($cnpj)) {
    Helper::jsonError('CNPJ inválido', ['cnpj' => 'CNPJ inválido']);
} else {
    $dados['cnpj'] = $cnpj;
}

// Validar dados obrigatórios
$campos = [
    'nome_social' => 'Nome social',
    'segmento' => 'Segmento',
    'endereco' => 'Endereço',
    'cidade' => 'Cidade',
    'estado' => 'Estado',
    'email' => 'Email',
    'telefone' => 'Telefone',
    'sobre' => 'Descrição'
];

foreach ($campos as $campo => $label) {
    $valor = Request::post($campo, '');
    if (empty($valor)) {
        $erros[] = "$label é obrigatório";
    } else {
        $dados[$campo] = limpar($valor);
    }
}

// Validações específicas
if (!Helper::validarEmail($dados['email'])) {
    $erros[] = 'E-mail inválido';
}

if (!Helper::validarTelefone($dados['telefone'])) {
    $erros[] = 'Telefone inválido';
}

if (strlen($dados['sobre']) < 50) {
    $erros[] = 'Descrição deve ter pelo menos 50 caracteres';
}

// Campos opcionais
$dados['cep'] = preg_replace('/[^0-9]/', '', Request::post('cep', ''));
$dados['site'] = limpar(Request::post('site', ''));
$dados['linkedin'] = limpar(Request::post('linkedin', ''));
$dados['funcionarios'] = Request::post('funcionarios', '');
$dados['fundacao'] = Request::post('fundacao', '');

// Validar URLs opcionais
if (!empty($dados['site']) && !filter_var($dados['site'], FILTER_VALIDATE_URL)) {
    $erros[] = 'Site inválido';
}

if (!empty($dados['linkedin']) && !filter_var($dados['linkedin'], FILTER_VALIDATE_URL)) {
    $erros[] = 'LinkedIn inválido';
}

// Processar upload da logo
$logoPath = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $files = new Files();
    $resultado = $files->upload($_FILES['logo'], 'logos', 'logo');
    
    if (!$resultado['status']) {
        $erros[] = $resultado['message'];
    } else {
        $logoPath = $resultado['path'];
    }
}

// Se houver erros
if (!empty($erros)) {
    Helper::jsonError('Erro na validação dos dados', $erros);
}

// Salvar no banco
try {
    $db = new Database();
    
    // Verificar se CNPJ já existe
    $existe = $db->table('empresas')
        ->where('cnpj', $cnpj)
        ->first();
    
    if ($existe) {
        Helper::jsonError('CNPJ já cadastrado');
    }
    
    // Inserir empresa
    $dadosInsert = [
        'cnpj' => $dados['cnpj'],
        'nome_social' => $dados['nome_social'],
        'segmento' => $dados['segmento'],
        'endereco' => $dados['endereco'],
        'cidade' => $dados['cidade'],
        'estado' => $dados['estado'],
        'cep' => $dados['cep'],
        'email' => $dados['email'],
        'telefone' => $dados['telefone'],
        'site' => $dados['site'],
        'linkedin' => $dados['linkedin'],
        'sobre' => $dados['sobre'],
        'funcionarios' => $dados['funcionarios'],
        'fundacao' => $dados['fundacao'],
        'logo' => $logoPath,
        'data_cadastro' => date('Y-m-d H:i:s'),
        'ativo' => 1
    ];
    
    $empresaId = $db->table('empresas')->insert($dadosInsert);
    
    if ($empresaId) {
        Helper::jsonSuccess('Empresa cadastrada com sucesso!', [
            'empresa_id' => $empresaId
        ]);
    } else {
        Helper::jsonError('Erro ao salvar empresa no banco de dados');
    }
    
} catch (\Exception $e) {
    Helper::jsonError('Erro ao salvar: ' . $e->getMessage());
}
