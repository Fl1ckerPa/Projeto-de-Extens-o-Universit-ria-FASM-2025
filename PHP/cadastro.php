<?php
/**
 * Processa cadastro de usuário (PF ou PJ) usando o esquema normalizado.
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$erros = [];

$dados = [
    'nome' => Helper::limpar(Request::post('nome', '')),
    'cpf' => preg_replace('/\D+/', '', Request::post('cpf', '')),
    'cnpj' => preg_replace('/\D+/', '', Request::post('cnpj', '')),
    'tipoCadastro' => strtolower(Helper::limpar(Request::post('tipoCadastro', ''))),
    'email' => strtolower(Helper::limpar(Request::post('email', ''))),
    'senha' => Request::post('senha', ''),
    'senhaVerif' => Request::post('senhaverif', '')
];

if (Helper::vazio($dados['nome'])) {
    $erros[] = 'O campo nome é obrigatório.';
}

if ($dados['tipoCadastro'] === 'pf') {
    if (Helper::vazio($dados['cpf'])) {
        $erros[] = 'CPF obrigatório.';
    } elseif (!Helper::validarCPF($dados['cpf'])) {
        $erros[] = 'CPF inválido.';
    }
} elseif ($dados['tipoCadastro'] === 'pj') {
    if (Helper::vazio($dados['cnpj'])) {
        $erros[] = 'CNPJ obrigatório.';
    } elseif (!Helper::validarCNPJ($dados['cnpj'])) {
        $erros[] = 'CNPJ inválido.';
    }
} else {
    $erros[] = 'Tipo de cadastro inválido (selecione Pessoa Física ou Jurídica).';
}

if (!Validator::make(['email' => $dados['email']], ['email' => ['label' => 'Email', 'rules' => 'required|email']])) {
    $erros = array_merge($erros, array_values(Validator::getErrors()));
}

if (Helper::vazio($dados['senha']) || Helper::vazio($dados['senhaVerif'])) {
    $erros[] = 'Ambos os campos de senha são obrigatórios.';
} elseif ($dados['senha'] !== $dados['senhaVerif']) {
    $erros[] = 'As senhas informadas não conferem.';
} elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $dados['senha'])) {
    $erros[] = 'A senha deve ter entre 8 e 20 caracteres, conter letras, números e ao menos um caractere especial.';
}

if (!empty($erros)) {
    $title = 'Erros no cadastro';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/cadastro.html' => 'Voltar ao cadastro'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

$db = new Database();
$pdo = $db->connect();

try {
    Schema::ensureNormalizedSchema($pdo);

    $pdo->beginTransaction();

    // Verificar duplicidade de email
    $stmt = $pdo->prepare('SELECT pessoa_id FROM pessoa WHERE email = ? LIMIT 1');
    $stmt->execute([$dados['email']]);
    if ($stmt->fetch()) {
        throw new \Exception('Email já cadastrado.');
    }

    // Verificar duplicidade de CPF (PF)
    if ($dados['tipoCadastro'] === 'pf') {
        $stmt = $pdo->prepare('SELECT pessoa_id FROM pessoa WHERE cpf = ? LIMIT 1');
        $stmt->execute([$dados['cpf']]);
        if ($stmt->fetch()) {
            throw new \Exception('CPF já cadastrado.');
        }
    }

    // Verificar duplicidade de CNPJ (PJ)
    if ($dados['tipoCadastro'] === 'pj') {
        $stmt = $pdo->prepare('SELECT empresa_id FROM empresa WHERE cnpj = ? LIMIT 1');
        $stmt->execute([$dados['cnpj']]);
        if ($stmt->fetch()) {
            throw new \Exception('CNPJ já cadastrado.');
        }
    }

    // Inserir ou recuperar cidade (opcional)
    $cidadeId = null;
    $cidade = Helper::limpar(Request::post('cidade', ''));
    $estado = strtoupper(Helper::limpar(Request::post('estado', '')));
    if (!Helper::vazio($cidade) && !Helper::vazio($estado)) {
    $stmt = $pdo->prepare('SELECT cidade_id FROM cidade WHERE cidade = ? AND uf = ? LIMIT 1');
    $stmt->execute([$cidade, $estado]);
    $rowCidade = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($rowCidade) {
            $cidadeId = (int)$rowCidade['cidade_id'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO cidade (cidade, uf) VALUES (?, ?)');
            $stmt->execute([$cidade, $estado]);
            $cidadeId = (int)$pdo->lastInsertId();
        }
    }

    // Inserir pessoa
    $stmt = $pdo->prepare('INSERT INTO pessoa (nome, cpf, email, ativo) VALUES (?, ?, ?, 1)');
    $stmt->execute([
        $dados['nome'],
        $dados['tipoCadastro'] === 'pf' ? $dados['cpf'] : null,
        $dados['email']
    ]);
    $pessoaId = (int)$pdo->lastInsertId();

    // Determinar tipo de usuário
    $codigoTipo = $dados['tipoCadastro'] === 'pj' ? 'ANUNC' : 'CONT';
    $stmt = $pdo->prepare('SELECT usuario_tipo_id FROM usuario_tipo WHERE codigo = ? LIMIT 1');
    $stmt->execute([$codigoTipo]);
    $tipoRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$tipoRow) {
        throw new \Exception('Tipo de usuário não configurado. Informe o administrador.');
    }
    $usuarioTipoId = (int)$tipoRow['usuario_tipo_id'];

    // Inserir usuário
    $stmt = $pdo->prepare('INSERT INTO usuario (pessoa_id, login, senha_hash, usuario_tipo_id, ativo) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([
        $pessoaId,
        $dados['email'],
        Helper::hashSenha($dados['senha']),
        $usuarioTipoId
    ]);
    $usuarioId = (int)$pdo->lastInsertId();

    $empresaId = null;
    if ($dados['tipoCadastro'] === 'pj') {
        $stmt = $pdo->prepare('INSERT INTO empresa (cnpj, nome_social, email, cidade_id, ativo) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute([
            $dados['cnpj'],
            $dados['nome'],
            $dados['email'],
            $cidadeId
        ]);
        $empresaId = (int)$pdo->lastInsertId();

        // Opcional: manter vínculo em sessão para uso posterior
        Session::set('empresa_cadastrada_id', $empresaId);
    }

    $pdo->commit();

    $mensagens = [
        'Cadastro realizado com sucesso!',
        '<strong>Nome:</strong> ' . htmlspecialchars($dados['nome']),
        $dados['tipoCadastro'] === 'pf'
            ? '<strong>CPF:</strong> ' . Helper::formatarCPF($dados['cpf'])
            : '<strong>CNPJ:</strong> ' . Helper::formatarCNPJ($dados['cnpj']),
        '<strong>Email:</strong> ' . htmlspecialchars($dados['email']),
        'Agora você pode fazer login no sistema.'
    ];

    if ($empresaId) {
        $mensagens[] = '<strong>ID da empresa cadastrada:</strong> ' . $empresaId;
    }

    $title = 'Cadastro realizado com sucesso!';
    $messages = $mensagens;
    $type = 'success';
    $links = ['../HTML/login.html' => 'Ir para login'];
    include __DIR__ . '/partials/layout.php';
    exit;

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $title = 'Erro no cadastro';
    $messages = ['Erro ao processar cadastro: ' . $e->getMessage()];
    $type = 'error';
    $links = ['../HTML/cadastro.html' => 'Voltar ao cadastro'];
    include __DIR__ . '/partials/layout.php';
    exit;
}
