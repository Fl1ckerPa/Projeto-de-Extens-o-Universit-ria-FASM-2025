<?php
/**
 * Validator - Classe para validação de dados
 * Adaptada do AtomPHP
 */

class Validator 
{
    public static function make(array $data, array $rules)
    {
        $errors = [];

        foreach ($rules as $ruleKey => $ruleValue) {
            $itensRule = explode("|", $ruleValue['rules']);

            if (isset($data[$ruleKey])) {
                foreach ($itensRule as $itemKey) {
                    $items = explode(":", $itemKey);

                    switch ($items[0]) {
                        case 'required':
                            if (($data[$ruleKey] == "") || (empty($data[$ruleKey]))) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> deve ser preenchido.";
                            }
                            break;

                        case 'email':
                            if (!filter_var($data[$ruleKey], FILTER_VALIDATE_EMAIL)) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> não é válido.";
                            }
                            break;

                        case 'float':
                            if (!filter_var($data[$ruleKey], FILTER_VALIDATE_FLOAT)) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> deve conter número decimal.";
                            }
                            break;

                        case 'int':
                            if (!filter_var($data[$ruleKey], FILTER_VALIDATE_INT)) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> deve conter número inteiro.";
                            }
                            break;

                        case "min":
                            if (strlen(strip_tags($data[$ruleKey])) < $items[1]) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> deve conter no mínimo " . $items[1] . " caracteres.";
                            }
                            break;
                        
                        case 'max':
                            if (strlen(strip_tags($data[$ruleKey])) > $items[1]) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> deve conter no máximo " . $items[1] . " caracteres.";
                            }
                            break;
                        
                        case 'date':
                            if (!validateDate($data[$ruleKey], 'Y-m-d')) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> está com formato incorreto (Y-m-d)";
                            }
                            break;

                        case 'datetime':
                            if (!validateDate($data[$ruleKey], 'Y-m-d H:i:s')) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> está com formato incorreto (Y-m-d H:i:s)";
                            }
                            break;

                        case 'cpf':
                            if (!Helper::validarCPF($data[$ruleKey])) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> não é um CPF válido.";
                            }
                            break;

                        case 'cnpj':
                            if (!Helper::validarCNPJ($data[$ruleKey])) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> não é um CNPJ válido.";
                            }
                            break;

                        case 'telefone':
                            if (!Helper::validarTelefone($data[$ruleKey])) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> não é um telefone válido.";
                            }
                            break;

                        case 'cep':
                            if (!Helper::validarCEP($data[$ruleKey])) {
                                $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> não é um CEP válido.";
                            }
                            break;
                    }
                }
            } else {
                // Verificar se é obrigatório
                if (in_array('required', explode("|", $ruleValue['rules']))) {
                    $errors[$ruleKey] = "O campo <b>{$ruleValue['label']}</b> é obrigatório.";
                }
            }
        }

        if (!empty($errors)) {
            Session::set('formErrors', $errors);
            Session::set('inputs', $data);
            return false;
        } else {
            Session::destroy('formErrors');
            Session::destroy('inputs');
            return true;
        }
    }

    /**
     * Retorna erros da última validação
     */
    public static function getErrors()
    {
        return Session::get('formErrors') ?: [];
    }
}

