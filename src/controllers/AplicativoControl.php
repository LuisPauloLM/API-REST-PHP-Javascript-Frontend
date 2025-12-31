<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../DAO/AplicativoDAO.php';
require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/Logger.php';

class AplicativoControl
{
    public function index(): never
    {
        $aplicativoDAO = new AplicativoDAO();
        $aplicativos = $aplicativoDAO->readAll();

        (new Response(
            success: true,
            message: 'Aplicativos listados com sucesso',
            data: ['aplicativos' => $aplicativos],
            httpCode: 200
        ))->send();
        exit();
    }

    public function show(int $idAplicativo): never
    {
        $aplicativoDAO = new AplicativoDAO();
        $aplicativo = $aplicativoDAO->readById($idAplicativo);

        if ($aplicativo) {
            (new Response(
                success: true,
                message: 'Aplicativo encontrado',
                data: ['aplicativo' => $aplicativo],
                httpCode: 200
            ))->send();
        } else {
            (new Response(
                success: false,
                message: 'Aplicativo não encontrado',
                httpCode: 404
            ))->send();
        }
        exit();
    }

    public function store(stdClass $stdAplicativo): never
    {
        $aplicativo = new Aplicativo();
        $aplicativo->setNomeAplicativo($stdAplicativo->aplicativo->nomeAplicativo);
        $aplicativo->setNomeEmpresa($stdAplicativo->aplicativo->nomeEmpresa ?? null);

        $aplicativoDAO = new AplicativoDAO();
        $novoAplicativo = $aplicativoDAO->create($aplicativo);

        (new Response(
            success: true,
            message: 'Aplicativo cadastrado com sucesso',
            data: ['aplicativo' => $novoAplicativo],
            httpCode: 201 // Created
        ))->send();
        exit();
    }

    public function edit(stdClass $stdAplicativo, int $idAplicativo): never
    {
        $aplicativo = (new Aplicativo())
            ->setIdAplicativo($idAplicativo)
            ->setNomeAplicativo($stdAplicativo->aplicativo->nomeAplicativo)
            ->setNomeEmpresa($stdAplicativo->aplicativo->nomeEmpresa ?? null);

        $aplicativoDAO = new AplicativoDAO();
        if ($aplicativoDAO->update($aplicativo)) {
            (new Response(
                success: true,
                message: 'Aplicativo atualizado com sucesso',
                data: ['aplicativo' => $aplicativo],
                httpCode: 200
            ))->send();
        } else {
            (new Response(
                success: false,
                message: 'Falha ao atualizar aplicativo',
                httpCode: 500
            ))->send();
        }
        exit();
    }

    public function destroy(int $idAplicativo): never
    {
        $aplicativoDAO = new AplicativoDAO();
        if ($aplicativoDAO->delete($idAplicativo)) {
            (new Response(
                success: true,
                message: 'Aplicativo excluído com sucesso',
                httpCode: 204 // No Content
            ))->send();
        } else {
            (new Response(
                success: false,
                message: 'Falha ao excluir aplicativo',
                httpCode: 500
            ))->send();
        }
        exit();
    }
}
