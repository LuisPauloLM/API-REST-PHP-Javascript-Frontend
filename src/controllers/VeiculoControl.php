<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Veiculo.php';
require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../DAO/VeiculoDAO.php';
require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/Logger.php';

class VeiculoControl
{
    public function index(): never
    {
        $veiculoDAO = new VeiculoDAO();
        $veiculos = $veiculoDAO->readAll();

        (new Response(
            success: true,
            message: 'Veículos listados com sucesso',
            data: ['veiculos' => $veiculos],
            httpCode: 200
        ))->send();
        exit();
    }

    public function show(int $idVeiculo): never
    {
        $veiculoDAO = new VeiculoDAO();
        $veiculo = $veiculoDAO->readById($idVeiculo);

        if ($veiculo) {
            (new Response(
                success: true,
                message: 'Veículo encontrado',
                data: ['veiculo' => $veiculo],
                httpCode: 200
            ))->send();
        } else {
            (new Response(
                success: false,
                message: 'Veículo não encontrado',
                httpCode: 404
            ))->send();
        }
        exit();
    }

    public function store(stdClass $stdVeiculo): never
    {
        $aplicativo = (new Aplicativo())->setIdAplicativo($stdVeiculo->veiculo->aplicativo_idAplicativo);

        $veiculo = new Veiculo();
        $veiculo->setNomeVeiculo($stdVeiculo->veiculo->nomeVeiculo);
        $veiculo->setUsado($stdVeiculo->veiculo->usado);
        $veiculo->setAplicativo($aplicativo);

        $veiculoDAO = new VeiculoDAO();
        $novoVeiculo = $veiculoDAO->create($veiculo);

        (new Response(
            success: true,
            message: 'Veículo cadastrado com sucesso',
            data: ['veiculo' => $novoVeiculo],
            httpCode: 201 // Created
        ))->send();
        exit();
    }

    public function edit(stdClass $stdVeiculo, int $idVeiculo): never
    {
        $aplicativo = (new Aplicativo())->setIdAplicativo($stdVeiculo->veiculo->aplicativo_idAplicativo);

        $veiculo = (new Veiculo())
            ->setIdVeiculo($idVeiculo)
            ->setNomeVeiculo($stdVeiculo->veiculo->nomeVeiculo)
            ->setUsado($stdVeiculo->veiculo->usado)
            ->setAplicativo($aplicativo);

        $veiculoDAO = new VeiculoDAO();
        if ($veiculoDAO->update($veiculo)) {
            (new Response(
                success: true,
                message: 'Veículo atualizado com sucesso',
                data: ['veiculo' => $veiculo],
                httpCode: 200
            ))->send();
        } else {
            (new Response(
                success: false,
                message: 'Falha ao atualizar veículo',
                httpCode: 500
            ))->send();
        }
        exit();
    }

    public function destroy(int $idVeiculo): never
    {
        $veiculoDAO = new VeiculoDAO();
        if ($veiculoDAO->delete($idVeiculo)) {
            (new Response(
                success: true,
                message: 'Veículo excluído com sucesso',
                httpCode: 204 // No Content
            ))->send();
        } else {
            (new Response(
                success: false,
                message: 'Falha ao excluir veículo',
                httpCode: 500
            ))->send();
        }
        exit();
    }
}
