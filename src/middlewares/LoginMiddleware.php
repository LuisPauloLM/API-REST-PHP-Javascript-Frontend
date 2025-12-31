<?php
require_once __DIR__ . '/../http/Response.php';

class LoginMiddleware
{
    public function stringJsonToStdClass($requestBody): stdClass
    {
        $stdLogin = json_decode($requestBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            (new Response(
                success: false,
                message: 'JSON inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'JSON inválido no corpo da requisição',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!isset($stdLogin->usuario)) {
            (new Response(
                success: false,
                message: 'Dados inválidos',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Objeto "usuario" não encontrado',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!isset($stdLogin->usuario->email)) {
            (new Response(
                success: false,
                message: 'Dados inválidos',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Campo "email" é obrigatório',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!isset($stdLogin->usuario->senha)) {
            (new Response(
                success: false,
                message: 'Dados inválidos',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Campo "senha" é obrigatório',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        return $stdLogin;
    }

    public function isValidEmail($email): self
    {
        if (empty($email)) {
            (new Response(
                success: false,
                message: 'Email inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Email não pode estar vazio'
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (strlen($email) < 5) {
            (new Response(
                success: false,
                message: 'Email inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Email deve ter pelo menos 5 caracteres'
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            (new Response(
                success: false,
                message: 'Email inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Formato de email inválido'
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        return $this;
    }

    public function isValidSenha(string $senha): self
    {
        if (empty($senha)) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Senha não pode estar vazia',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (strlen($senha) < 8) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Senha deve ter no mínimo 8 caracteres',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!preg_match('/[A-Z]/', $senha)) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Senha deve conter pelo menos uma letra maiúscula',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!preg_match('/[a-z]/', $senha)) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Senha deve conter pelo menos uma letra minúscula',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!preg_match('/[0-9]/', $senha)) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Senha deve conter pelo menos um número',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!preg_match('/[\W_]/', $senha)) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'Senha deve conter pelo menos um caractere especial',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        return $this;
    }
}
