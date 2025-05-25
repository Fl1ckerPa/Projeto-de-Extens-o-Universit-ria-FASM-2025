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
        $erros = "O campo e-mail é obrigatorio.";
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
        echo "<h3>Erros encontrados:</hr><ul>";
        foreach ($erros as $erro) {
            echo "<li>$erro</li>";
        }
        echo "</ul><a href='javascript:history.back()'>Voltar</a>";
    } else {
        echo "<h3>Login validado com sucesso!</h3>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Senha:</strong> (oculta)</p>";
        echo "<a href='painel.html'>Ir para o painel</a>";
    }
} else {
    echo "Acesso inválido.";
}
?>