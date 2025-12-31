<?php

declare(strict_types=1);

require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../DAO/VeiculoDAO.php';
require_once __DIR__ . '/../DAO/AplicativoDAO.php';

class VeiculoMiddleware
{
    public function stringJsonToStdClass(string $requestBody): stdClass
    {
        $stdVeiculo = json_decode($requestBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'JSON inválido',
                'data' => [],
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'O corpo da requisição não é um JSON válido.',
                ]
            ]);
            exit();
        }

        if (!isset($stdVeiculo->veiculo)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Objeto "veiculo" ausente',
                'data' => [],
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'O objeto "veiculo" é obrigatório no corpo da requisição.',
                ]
            ]);
            exit();
        }

        $requiredFields = ['nomeVeiculo', 'usado', 'aplicativo_idAplicativo'];
        foreach ($requiredFields as $field) {
            if (!isset($stdVeiculo->veiculo->$field)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => "Campo '{$field}' ausente",
                    'data' => [],
                    'error' => [
                        'code' => 'validation_error',
                        'message' => "O campo '{$field}' é obrigatório dentro do objeto 'veiculo'.",
                    ]
                ]);
                exit();
            }
        }

        return $stdVeiculo;
    }

    public function isValidId(int $idVeiculo): self
    {
        if ($idVeiculo <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de Veículo inválido',
                'data' => [],
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'O ID do veículo deve ser um número inteiro positivo.',
                ]
            ]);
            exit();
        }
        return $this;
    }

    public function isValidNomeVeiculo(string $nomeVeiculo): self
    {
        $nomeVeiculo = trim($nomeVeiculo);
        if (empty($nomeVeiculo) || strlen($nomeVeiculo) < 3) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Nome do Veículo inválido',
                'data' => [],
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'O nome do veículo deve ter pelo menos 3 caracteres.',
                ]
            ]);
            exit();
        }
        return $this;
    }

    public function isValidUsado(int $usado): self
    {
        if ($usado !== 0 && $usado !== 1) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Campo "usado" inválido',
                'data' => [],
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'O campo "usado" deve ser 0 (não) ou 1 (sim).',
                ]
            ]);
            exit();
        }
        return $this;
    }

    public function hasVeiculoById(int $idVeiculo): self
    {
        $veiculoDAO = new VeiculoDAO();
        $veiculo = $veiculoDAO->readById($idVeiculo);
        if ($veiculo === null) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Veículo não encontrado',
                'data' => [],
                'error' => [
                    'code' => 'not_found',
                    'message' => "Nenhum veículo encontrado com o ID '{$idVeiculo}'.",
                ]
            ]);
            exit();
        }
        return $this;
    }

    public function hasAplicativoById(int $idAplicativo): self
    {
        $aplicativoDAO = new AplicativoDAO();
        $aplicativo = $aplicativoDAO->readById($idAplicativo);
        if ($aplicativo === null) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Aplicativo associado não encontrado',
                'data' => [],
                'error' => [
                    'code' => 'validation_error',
                    'message' => "O Aplicativo com ID '{$idAplicativo}' não existe.",
                ]
            ]);
            exit();
        }
        return $this;
    }
}
