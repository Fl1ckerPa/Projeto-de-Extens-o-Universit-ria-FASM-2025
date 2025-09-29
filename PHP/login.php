<?php

//limpeza de campos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function limparEntrada($dado) {
        return htmlspecialchars(stripslashes(trim($dado)));
    }

    // coleta de dados
    $tipo = strtolower(limparEntrada($_POST["tipoCadastro"] ?? ''));
    $cpf = limparEntrada($_POST["cpf"] ?? '');
    $cnpj = limparEntrada($_POST["cnpj"] ?? '');
    $senha = $_POST["senha"] ?? '';

    // normalização: manter apenas dígitos em CPF/CNPJ
    $cpfNumeros = preg_replace('/\D+/', '', $cpf ?? '');
    $cnpjNumeros = preg_replace('/\D+/', '', $cnpj ?? '');

    $erros = [];

    // validar tipo + identificador
    if ($tipo === 'pf') {
        if (empty($cpfNumeros)) {
            $erros[] = "O campo CPF é obrigatório para Pessoa Física.";
        } elseif (strlen($cpfNumeros) !== 11) {
            $erros[] = "CPF inválido. Informe 11 dígitos.";
        }
    } elseif ($tipo === 'pj') {
        if (empty($cnpjNumeros)) {
            $erros[] = "O campo CNPJ é obrigatório para Pessoa Jurídica.";
        } elseif (strlen($cnpjNumeros) !== 14) {
            $erros[] = "CNPJ inválido. Informe 14 dígitos.";
        }
    } else {
        // fallback: tentar inferir pelo preenchimento
        if (!empty($cpfNumeros) && empty($cnpjNumeros)) {
            $tipo = 'pf';
            if (strlen($cpfNumeros) !== 11) {
                $erros[] = "CPF inválido. Informe 11 dígitos.";
            }
        } elseif (!empty($cnpjNumeros) && empty($cpfNumeros)) {
            $tipo = 'pj';
            if (strlen($cnpjNumeros) !== 14) {
                $erros[] = "CNPJ inválido. Informe 14 dígitos.";
            }
        } else {
            $erros[] = "Selecione o tipo de cadastro (PF ou PJ) e preencha CPF ou CNPJ.";
        }
    }

    // Validar senha
    if (empty($senha)) {
        $erros[] = "O campo senha é obrigatório.";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senha)) {
        $erros[] = "A senha deve conter entre 8 e 20 caracteres, letras, números e ao menos um caractere especial.";
    }

    // exibir erros ou sucesso
    if (!empty($erros)) {
        $title = 'Erros no login';
        $messages = $erros;
        $type = 'error';
        $links = [ '../HTML/login.html' => 'Voltar ao login' ];
        include __DIR__ . '/partials/layout.php';
    } else {
        $title = 'Login validado com sucesso!';
        $idLabel = $tipo === 'pj' ? 'CNPJ' : 'CPF';
        $idValor = $tipo === 'pj' ? $cnpjNumeros : $cpfNumeros;
        // mascarar identificação exibida (mostrar apenas últimos dígitos)
        $mascarado = str_repeat('•', max(0, strlen($idValor) - 4)) . substr($idValor, -4);
        $messages = [
          '<strong>Tipo:</strong> ' . strtoupper($tipo),
          '<strong>' . $idLabel . ':</strong> ' . $mascarado,
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