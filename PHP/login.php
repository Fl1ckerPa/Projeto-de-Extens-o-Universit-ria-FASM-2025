<?php

//limpeza de campos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function limparEntrada($dado) {
        return htmlspecialchars(stripslashes(trim($dado)));
}
//

    //coleta de dados
    $email = limparEntrada($_POST["email"] ?? '');
    $senha = $_POST["senha"] ?? '';

    $erros = [];
    //

    //validar email
    if (empty($email)) {
        $erros[] = "O campo e-mail é obrigatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido.";
    }

    //Validar senha
    if (empty($senha)) {
        $erros[] = "O campo senha é obrigatório.";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senha)) {
        $erros[] = "A senha deve conter entre 8 e 20 caracteres, letras, números e ao menos um caractere especial.";
    }
    //
    
    //exibir erros ou acerto
    if (!empty($erros)) {
        $title = 'Erros no login';
        $messages = $erros;
        $type = 'error';
        $links = [ '../HTML/login.html' => 'Voltar ao login' ];
        include __DIR__ . '/partials/layout.php';
    } else {
        $title = 'Login validado com sucesso!';
        $messages = [
          '<strong>Email:</strong> ' . htmlspecialchars($email),
          '<strong>Senha:</strong> (oculta)'
        ];
        $type = 'success';
        $links = [ '../HTML/dashboard.html' => 'Ir para o painel' ];
        include __DIR__ . '/partials/layout.php';
    }
} else {
    echo "Acesso inválido.";
}
?>