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
    'endereco' => 'Endereço (logradouro, número, complemento, bairro)',
    'cidade' => 'Cidade (nome)',
    'estado' => 'UF',
    'email' => 'Email',
    'telefone' => 'Telefone',
    'sobre' => 'Descrição'
];

foreach ($campos as $campo => $label) {
    $valor = Request::post($campo, '');
    if (empty($valor)) {
        $erros[] = "$label é obrigatório";
    } else {
        $dados[$campo] = Helper::limpar($valor);
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
$dados['site'] = Helper::limpar(Request::post('site', ''));
$dados['linkedin'] = Helper::limpar(Request::post('linkedin', ''));
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
    // Verificar se empresa já existe (por CNPJ ou e-mail)
    $sqlExisteEmpresa = "SELECT empresa_id FROM empresa WHERE cnpj = ? OR email = ? LIMIT 1";
    $resultExisteEmpresa = $db->dbSelect($sqlExisteEmpresa, [$dados['cnpj'], $dados['email']]);
    $existeEmpresa = $db->dbBuscaArray($resultExisteEmpresa);
    if ($existeEmpresa) {
        Helper::jsonError('Email ou CNPJ já cadastrado');
        return;
    }

    // Resolver cidade_id (criar se não existir)
    $cidadeNome = trim($dados['cidade']);
    $uf = strtoupper(trim($dados['estado']));
    $cidadeId = null;
    try {
        $rsCidade = $db->dbSelect("SELECT cidade_id FROM cidade WHERE cidade = ? AND uf = ? LIMIT 1", [$cidadeNome, $uf]);
        $cidadeRow = $db->dbBuscaArray($rsCidade);
        if ($cidadeRow) {
            $cidadeId = (int)$cidadeRow['cidade_id'];
        } else {
            $cidadeId = (int)$db->dbInsert("INSERT INTO cidade (cidade, uf) VALUES (?, ?)", [$cidadeNome, $uf]);
        }
    } catch (\Exception $e) {
        // Se a cidade não puder ser criada, segue nulo e o endereço fica sem FK
        error_log('Cidade não encontrada/criada: ' . $e->getMessage());
    }

    // Quebrar endereço livre (quando possível). Mantemos tudo em logradouro se não houver separação
    $logradouro = $dados['endereco'];
    $numero = null;
    $complemento = null;
    $bairro = null;

    // Inserir empresa normalizada
    $empresaId = $db->dbInsert(
        "INSERT INTO empresa (cnpj, nome_social, email, site, linkedin, sobre, funcionarios, fundacao,
                              logradouro, numero, complemento, bairro, cep, cidade_id, logo, ativo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
        [
            $dados['cnpj'],
            $dados['nome_social'],
            $dados['email'],
            $dados['site'] ?: null,
            $dados['linkedin'] ?: null,
            $dados['sobre'],
            $dados['funcionarios'] ?: null,
            $dados['fundacao'] ?: null,
            $logradouro,
            $numero,
            $complemento,
            $bairro,
            $dados['cep'] ?: null,
            $cidadeId,
            $logoPath ?: null
        ]
    );

    // Inserir/associar telefone
    $numeroTelefone = preg_replace('/[^0-9+]/', '', $dados['telefone']);
    $telefoneId = null;
    if (!empty($numeroTelefone)) {
        try {
            $rsTel = $db->dbSelect("SELECT telefone_id FROM telefone WHERE numero = ? LIMIT 1", [$numeroTelefone]);
            $telRow = $db->dbBuscaArray($rsTel);
            if ($telRow) {
                $telefoneId = (int)$telRow['telefone_id'];
            } else {
                $telefoneId = (int)$db->dbInsert("INSERT INTO telefone (numero, tipo) VALUES (?, ?)", [$numeroTelefone, 'mobile']);
            }
            // Vincular à empresa
            $db->dbInsert("INSERT IGNORE INTO empresa_telefone (empresa_id, telefone_id, principal) VALUES (?, ?, 1)", [$empresaId, $telefoneId]);
        } catch (\Exception $e) {
            error_log('Erro ao salvar telefone: ' . $e->getMessage());
        }
    }

    Helper::jsonSuccess('Empresa cadastrada com sucesso!', [
        'empresa_id' => $empresaId
    ]);
    
} catch (\Exception $e) {
    Helper::jsonError('Erro ao salvar: ' . $e->getMessage());
}
