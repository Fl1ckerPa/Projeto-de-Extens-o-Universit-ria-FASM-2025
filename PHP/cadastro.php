<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function limparEntrada($dado) {
        return htmlspecialchars(stripslashes(trim($dado)));
}

//coleta de dados
$nome = limparEntrada($_POST["nome"] ?? '');
$cpf = limparEntrada($_POST["cpf"] ?? '');
$cnpj = limparEntrada($_POST["cnpj"] ?? '');
$tipoCadastro = limparEntrada($_POST["tipoCadastro"] ?? '');
$email = limparEntrada($_POST["email"] ?? '');
$senha = $_POST["senha"] ?? '';
$senhaVerif = $_POST["senhaverif"] ?? '';

$erros = [];

// Remover máscaras
$cpf = preg_replace('/\D+/', '', $cpf);
$cnpj = preg_replace('/\D+/', '', $cnpj);

if (empty($nome)) {
    $erros[] = "O campo nome é Obrigatorio!";
}

if ($tipoCadastro === 'pf') {
    if (empty($cpf)) {
        $erros[] = "CPF obrigatório!";
    } elseif (!preg_match('/^\d{11}$/', $cpf)) {
        $erros[] = "O CPF deve conter exatamente 11 números.";
    }
} elseif ($tipoCadastro === 'pj') {
    if (empty($cnpj)) {
        $erros[] = "CNPJ obrigatório!";
    } elseif (!preg_match('/^\d{14}$/', $cnpj)) {
        $erros[] = "O CNPJ deve conter exatamente 14 números.";
    }
} else {
    $erros[] = "Tipo de cadastro inválido (selecione PF ou PJ).";
}

 if (empty($email)) {
    $erros[] = "O campo email é obrigatório.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Email inválido.";
}

if (empty($senha) || empty($senhaVerif)) {
    $erros[] = "Ambos os campos de senha são obrigatórios.";
} elseif ($senha !== $senhaVerif) {
    $erros[] = "As senhas não sao identicas.";   
} elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senha)) {
    $erros[] = "A senha deve ter entre 8 e 20 caracteres, conter letras, números e um caractere especial.";
}

if (!empty($erros)) {
    $title = 'Erros no cadastro';
    $messages = $erros;
    $type = 'error';
    $links = [ '../HTML/cadastro.html' => 'Voltar ao cadastro' ];
    include __DIR__ . '/partials/layout.php';
} else {
    $title = 'Cadastro validado com sucesso!';
    $messages = [
        '<strong>Nome:</strong> ' . htmlspecialchars($nome),
        ($tipoCadastro === 'pf' ? ('<strong>CPF:</strong> ' . htmlspecialchars($cpf)) : ('<strong>CNPJ:</strong> ' . htmlspecialchars($cnpj))),
        '<strong>Email:</strong> ' . htmlspecialchars($email),
        '<strong>Senha:</strong> (oculta por segurança)'
    ];
    $type = 'success';
    $links = [ '../HTML/login.html' => 'Ir para login' ];
    include __DIR__ . '/partials/layout.php';
}    
} else {
    echo "Acesso inválido.";
    }
?>