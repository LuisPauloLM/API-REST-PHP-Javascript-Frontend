<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../DAO/UsuarioDAO.php';
require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/Logger.php';

class UsuarioControl
{
    public function index(): never
    {
        $usuarioDAO = new UsuarioDAO();
        $usuarios = $usuarioDAO->readAll();

        $response = new Response(
            success: true,
            message: 'Usuários listados com sucesso',
            data: ['usuarios' => $usuarios],
            httpCode: 200
        );
        $response->send();
        exit();
    }

    public function show(int $idUsuario): never
    {
        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->readById($idUsuario);

        if ($usuario) {
            $response = new Response(
                success: true,
                message: 'Usuário encontrado',
                data: ['usuario' => $usuario],
                httpCode: 200
            );
            $response->send();
        } else {
            $response = new Response(
                success: false,
                message: 'Usuário não encontrado',
                httpCode: 404
            );
            $response->send();
        }
        exit();
    }

    public function store(stdClass $stdUsuario): never
    {
        $aplicativo = (new Aplicativo())->setIdAplicativo($stdUsuario->usuario->aplicativo_idAplicativo);

        $usuario = new Usuario();
        $usuario->setNomeUsuario($stdUsuario->usuario->nomeUsuario);
        $usuario->setEmail($stdUsuario->usuario->email);
        $usuario->setSenha(password_hash($stdUsuario->usuario->senha, PASSWORD_DEFAULT)); // Hash da senha
        $usuario->setMaiorIdade($stdUsuario->usuario->maiorIdade);
        $usuario->setAplicativo($aplicativo);

        // Define tipo padrão se não fornecido
        if (isset($stdUsuario->usuario->tipo)) {
            $usuario->setTipo($stdUsuario->usuario->tipo);
        } else {
            $usuario->setTipo('usuario'); // Tipo padrão
        }

        $usuarioDAO = new UsuarioDAO();
        $novoUsuario = $usuarioDAO->create($usuario);

        $response = new Response(
            success: true,
            message: 'Usuário cadastrado com sucesso',
            data: ['usuario' => $novoUsuario],
            httpCode: 201 // Created
        );
        $response->send();
        exit();
    }

    public function edit(stdClass $stdUsuario, int $idUsuario): never
    {
        $aplicativo = (new Aplicativo())->setIdAplicativo($stdUsuario->usuario->aplicativo_idAplicativo);

        $usuario = (new Usuario())
            ->setIdUsuario($idUsuario)
            ->setNomeUsuario($stdUsuario->usuario->nomeUsuario)
            ->setEmail($stdUsuario->usuario->email)
            ->setMaiorIdade($stdUsuario->usuario->maiorIdade)
            ->setAplicativo($aplicativo);

        // Atualiza tipo se fornecido
        if (isset($stdUsuario->usuario->tipo)) {
            $usuario->setTipo($stdUsuario->usuario->tipo);
        }

        // Se a senha for fornecida, atualiza. Caso contrário, mantém a existente.
        if (isset($stdUsuario->usuario->senha) && !empty($stdUsuario->usuario->senha)) {
            $usuario->setSenha(password_hash($stdUsuario->usuario->senha, PASSWORD_DEFAULT));
        } else {
            // Buscar a senha existente do banco para não sobrescrever com vazio/nulo
            $existingUser = (new UsuarioDAO())->readById($idUsuario);
            if ($existingUser) {
                $usuario->setSenha($existingUser->getSenhaHash()); // Usa método específico para hash
            }
        }

        $usuarioDAO = new UsuarioDAO();
        if ($usuarioDAO->update($usuario)) {
            $response = new Response(
                success: true,
                message: 'Usuário atualizado com sucesso',
                data: ['usuario' => $usuario],
                httpCode: 200
            );
            $response->send();
        } else {
            $response = new Response(
                success: false,
                message: 'Falha ao atualizar usuário',
                httpCode: 500
            );
            $response->send();
        }
        exit();
    }

    public function destroy(int $idUsuario): never
    {
        $usuarioDAO = new UsuarioDAO();
        if ($usuarioDAO->delete($idUsuario)) {
            $response = new Response(
                success: true,
                message: 'Usuário excluído com sucesso',
                httpCode: 204 // No Content
            );
            $response->send();
        } else {
            $response = new Response(
                success: false,
                message: 'Falha ao excluir usuário',
                httpCode: 500
            );
            $response->send();
        }
        exit();
    }
}
