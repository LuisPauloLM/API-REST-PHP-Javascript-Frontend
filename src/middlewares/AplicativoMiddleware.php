<?php

declare(strict_types=1);

require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../DAO/AplicativoDAO.php';

class AplicativoMiddleware
{
    public function stringJsonToStdClass(string $requestBody): stdClass
    {
        $stdAplicativo = json_decode($requestBody);

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

        if (!isset($stdAplicativo->aplicativo)) {
            (new Response(
                success: false,
                message: 'Objeto "aplicativo" ausente',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O objeto "aplicativo" é obrigatório no corpo da requisição.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        if (!isset($stdAplicativo->aplicativo->nomeAplicativo)) {
            (new Response(
                success: false,
                message: 'Campo "nomeAplicativo" ausente',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O campo "nomeAplicativo" é obrigatório dentro do objeto "aplicativo".',
                ],
                httpCode: 400
            ))->send();
            exit();
        }

        return $stdAplicativo;
    }

    public function isValidId(int $idAplicativo): self
    {
        if ($idAplicativo <= 0) {
            (new Response(
                success: false,
                message: 'ID de Aplicativo inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O ID do aplicativo deve ser um número inteiro positivo.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function isValidNomeAplicativo(string $nomeAplicativo): self
    {
        $nomeAplicativo = trim($nomeAplicativo);
        if (empty($nomeAplicativo) || strlen($nomeAplicativo) < 3) {
            (new Response(
                success: false,
                message: 'Nome do Aplicativo inválido',
                error: [
                    'code' => 'validation_error',
                    'message' => 'O nome do aplicativo deve ter pelo menos 3 caracteres.',
                ],
                httpCode: 400
            ))->send();
            exit();
        }
        return $this;
    }

    public function hasNotAplicativoByName(string $nomeAplicativo): self
    {
        $aplicativoDAO = new AplicativoDAO();
        $aplicativo = $aplicativoDAO->readByName($nomeAplicativo);
        if ($aplicativo !== null) {
            (new Response(
                success: false,
                message: 'Aplicativo já cadastrado',
                error: [
                    'code' => 'validation_error',
                    'message' => "Já existe um aplicativo com o nome '{$nomeAplicativo}'.",
                ],
                httpCode: 409 // Conflict
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
                message: 'Aplicativo não encontrado',
                error: [
                    'code' => 'not_found',
                    'message' => "Nenhum aplicativo encontrado com o ID '{$idAplicativo}'.",
                ],
                httpCode: 404
            ))->send();
            exit();
        }
        return $this;
    }
}
