<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function limparEntrada($dado) {
        return htmlspecialchars(stripslashes(trim($dado)));
}

//coleta de dados
$nome = limparEntrada($_POST["nome"] ?? '');
$cpf = limparEntrada($_POST["cpf"] ?? '');
$email = limparEntrada($_POST["email"] ?? '');
$senha = $_POST["senha"] ?? '';
$senhaVerif = $_POST["email"] ?? '';

$erros = [];

if (empty($nome)) {
    $erros[] = "O campo nome é Obrigatorio!";
}

if (empty($cpf)) {
    $erros[] = "CPF obrigatorio!";
} elseif (!preg_match('/^\d{11}$/', $cpf)) {
    $erros[] = "O CPF deve conter exatamente 11 números";
}

 if (empty($email)) {
    $erros[] = "O campo email é obrigatório.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Email inválido.";
}

if (empty($senha) || empty($senhaVerif)) {
    $erros[] = "Ambos os campos de senha são obrigatórios.";
} elseif ($senha !== $senhaVerif) {
    $erros = "As senhas não sao identicas.";   
} elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senha)) {
    $erros = "A senha deve ter entre 8 e 20 caracteres, conter letras, números e um caractere especial.";
}

if (!empty($erros)) {
    echo "<h3>Erros encontrados:<h3><ul>";
    foreach ($erros as $erro) {
        echo "<li>$erro</li>";
    }
    echo "</ul><a href='javascript:history.back()'>Voltar</a>";
} else {
     echo "<h3>Cadastro validado com sucesso!</h3>";
        echo "<p><strong>Nome:</strong> $nome</p>";
        echo "<p><strong>CPF:</strong> $cpf</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        //esconder senha
        echo "<p><strong>Senha:</strong> (oculta por segurança)</p>";
        echo "<a href='login.html'>Ir para login</a>";
    }    
} else {
    echo "Acesso inválido.";
    }
?>