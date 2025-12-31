<?php

declare(strict_types=1);

require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../DAO/UsuarioDAO.php';
require_once __DIR__ . '/../DAO/AplicativoDAO.php';

class UsuarioMiddleware
{
    public function stringJsonToStdClass(string $requestBody): stdClass
    {
        $stdUsuario = json_decode($requestBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            (new Response(
                success: false,
                message: 'JSON inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O corpo da requisição não é um JSON válido.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!isset($stdUsuario->usuario)) {
            (new Response(
                success: false,
                message: 'Objeto "usuario" ausente',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O objeto "usuario" é obrigatório no corpo da requisição.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        $requiredFields = ['nomeUsuario', 'email', 'senha', 'maiorIdade', 'aplicativo_idAplicativo'];
        foreach ($requiredFields as $field) {
            if (!isset($stdUsuario->usuario->$field)) {
                (new Response(
                    success: false,
                    message: "Campo '{$field}' ausente",
                    error: [
                        'code' => 'validation_error',
                        'message' => "O campo '{$field}' é obrigatório dentro do objeto 'usuario'.",
                    ],
                    httpCode: 400
                ))->send();
                exit();
            }
        }

        return $stdUsuario;
    }

    public function isValidId(int $idUsuario): self
    {
        if ($idUsuario <= 0) {
            (new Response(
                success: false,
                message: 'ID de Usuário inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O ID do usuário deve ser um número inteiro positivo.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function isValidNomeUsuario(string $nomeUsuario): self
    {
        $nomeUsuario = trim($nomeUsuario);
        if (empty($nomeUsuario) || strlen($nomeUsuario) < 3) {
            (new Response(
                success: false,
                message: 'Nome de Usuário inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O nome do usuário deve ter pelo menos 3 caracteres.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function isValidEmail(string $email): self
    {
        $email = trim($email);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            (new Response(
                success: false,
                message: 'Email inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O email fornecido é inválido.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function isValidSenha(string $senha): self
    {
        // Mínimo 8 caracteres, pelo menos uma maiúscula, uma minúscula, um número, um caractere especial
        if (
            strlen($senha) < 8 ||
            !preg_match('/[A-Z]/', $senha) ||
            !preg_match('/[a-z]/', $senha) ||
            !preg_match('/[0-9]/', $senha) ||
            !preg_match('/[\W_]/', $senha)
        ) {
            (new Response(
                success: false,
                message: 'Senha inválida',
                error: [
                    'code' => 'validation_error',
                    'message' => 'A senha deve ter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e caracteres especiais.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function isValidMaiorIdade(int $maiorIdade): self
    {
        if ($maiorIdade !== 0 && $maiorIdade !== 1) {
            (new Response(
                success: false,
                message: 'Campo "maiorIdade" inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O campo "maiorIdade" deve ser 0 (não) ou 1 (sim).',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function hasNotUsuarioByEmail(string $email): self
    {
        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->readByEmail($email);
        if ($usuario !== null) {
            (new Response(
                success: false,
                message: 'Email já cadastrado',
                error: [
                    'code' => 'validation_error',
                    'message' => "Já existe um usuário com o email '{$email}'.",
                ],
                httpCode: 409 // Conflict
            ))->send();
            exit();
        }
        return $this;
    }

    public function hasUsuarioById(int $idUsuario): self
    {
        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->readById($idUsuario);
        if ($usuario === null) {
            (new Response(
                success: false,
                message: 'Usuário não encontrado',
                error: [
                    'code' => 'not_found',
                    'message' => "Nenhum usuário encontrado com o ID '{$idUsuario}'.",
                ],
                httpCode: 404
            ))->send();
            exit();
        }
        return $this;
    }

    public function hasAplicativoById(int $idAplicativo): self
    {
        $aplicativoDAO = new AplicativoDAO();
        $aplicativo = $aplicativoDAO->readById($idAplicativo);
        if ($aplicativo === null) {
            (new Response(
                success: false,
                message: 'Aplicativo associado não encontrado',
                error: [
                    'code' => 'validation_error',
                    'message' => "O Aplicativo com ID '{$idAplicativo}' não existe.",
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }
}
