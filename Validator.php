<?php


class Validator
{

    public static function validate($form = [], $campos = [])
    {

        $erros = [];

        foreach ($campos as $campo => $valor) {
            $validacao = explode("|", $valor);

            //Verifica se o campo informado e do tipo obrigatorio
            if (empty($form[$campo]) && !is_numeric($form[$campo]) && in_array('required', $validacao)) {
                if (in_array('password', $validacao)) {
                    $erros["erro_" . $campo . ""] = "A $campo é obrigatória";
                } else {
                    $erros["erro_" . $campo . ""] = "O $campo é obrigatório";
                }
            } else {

                //Verifica se o campo email esta corretamente informado
                if (in_array('email', $validacao)) {
                    if (!filter_var($form[$campo], FILTER_VALIDATE_EMAIL)) {
                        $erros["erro_" . $campo . ""] = "Formato de email inválido";
                }

                //Verifica se a senha informada corresponde ao mininimo de requisito para o sistema
                if (in_array('password', $validacao)) {
                    $uppercase = preg_match('@[A-Z]@', $form[$campo]);
                    $lowercase = preg_match('@[a-z]@', $form[$campo]);
                    $number    = preg_match('@[0-9]@', $form[$campo]);
                    $specialChars = preg_match('@[^\w]@', $form[$campo]);
                    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($form[$campo]) < 8) {
                        $erros["erro_" . $campo . ""] = "A " . $campo . " deve ter no mínimo 8 caracteres, sendo, uma letra maiscula, um número, e um caractere especial!";
                    }
                }

                //Verifica se o valor informado no campo e do tipo inteiro
                if (!is_numeric($form[$campo]) && in_array('integer', $validacao)) {
                    $integer = (int)$form[$campo];
                    if (!is_int($integer)) {
                        $erros["erro_" . $campo . ""] = "O valor do " . $campo . " deve ser do tipo integer";
                    }
                }

                //Verifica se o valor informado no campo e do tipo string
                if (!is_string($form[$campo]) && in_array('string', $validacao)) {
                    $erros["erro_" . $campo . ""] = "O valor do " . $campo . " deve ser do tipo string";
                }

                //Verifica se o valor informado no campo e do tipo boolean
                if (!is_bool($form[$campo]) && in_array('boolean', $validacao)) {
                    $erros["erro_" . $campo . ""] = "O valor do " . $campo . " deve ser do tipo boolean";
                }

                //Verifica se o valor informado no campo e do tipo array
                if (!is_array($form[$campo]) && in_array('array', $validacao)) {
                    $erros["erro_" . $campo . ""] = "O valor do " . $campo . " deve ser do tipo array";
                }

                //Verifica se a data informada e válida
                if (in_array('date', $validacao)) {
                    $d = DateTime::createFromFormat('Y-m-d', $form[$campo]);
                    $validDate =  $d && $d->format('Y-m-d') === $form[$campo];
                    if ($validDate == false) {
                        $erros["erro_" . $campo . ""] = "A " . $campo . " é inválida";
                    }
                }

                //Se o campo seja do tipo cpf verifica se o valor informado esta correto
                if (in_array('cpf', $validacao)) {
                    $cpf = preg_replace('/[^0-9]/is', '', $form[$campo]);

                    // Verifica se foi informado todos os digitos corretamente
                    if (strlen($cpf) != 11) {
                        $erros["erro_" . $campo . ""] = "O " . $campo . " é inválido";
                    }

                    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
                    if (preg_match('/(\d)\1{10}/', $cpf)) {
                        $erros["erro_" . $campo . ""] = "O " . $campo . " é inválido";
                    }

                    // Faz o calculo para validar o CPF
                    for ($t = 9; $t < 11; $t++) {
                        for ($d = 0, $c = 0; $c < $t; $c++) {
                            $d += $cpf[$c] * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ($cpf[$c] != $d) {
                            $erros["erro_" . $campo . ""] = "O " . $campo . " informado é inválido";
                        }
                    }
                }

                //Se o campo for do tipo cnpj verifica se o valor informado esta corret
                if (in_array('cnpj', $validacao)) {
                    $cnpj = preg_replace('/[^0-9]/', '', (string) $form[$campo]);

                    // Valida tamanho
                    if (strlen($cnpj) != 14) {
                        $erros["erro_" . $campo . ""] = "O " . $campo . " informado é inválido";
                    }


                    // Verifica se todos os digitos são iguais
                    if (preg_match('/(\d)\1{13}/', $cnpj)) {
                        $erros["erro_" . $campo . ""] = "O " . $campo . " informado é inválido";
                    }


                    // Valida primeiro dígito verificador
                    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
                        $soma += $cnpj[$i] * $j;
                        $j = ($j == 2) ? 9 : $j - 1;
                    }

                    $resto = $soma % 11;

                    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
                        $erros["erro_" . $campo . ""] = "O " . $campo . " informado é inválido";
                    }

                    // Valida segundo dígito verificador
                    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
                        $soma += $cnpj[$i] * $j;
                        $j = ($j == 2) ? 9 : $j - 1;
                    }

                    $resto = $soma % 11;

                    if (($cnpj[13] == ($resto < 2 ? 0 : 11 - $resto)) == false) {
                        $erros["erro_" . $campo . ""] = "O " . $campo . " informado é inválido";
                    }
                }
            }
        }

        return $erros;
    }
}
