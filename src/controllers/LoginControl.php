<?php
require_once __DIR__ . '/../DAO/LoginDAO.php';
require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/MeuTokenJWT.php';

class LoginControl
{
    public function autenticar(stdClass $stdLogin): void
    {
        $loginDAO = new LoginDAO();
        $usuario = new Usuario();

        $usuario->setEmail($stdLogin->usuario->email);
        $usuario->setSenha($stdLogin->usuario->senha);

        $usuarioLogado = $loginDAO->verificarLogin($usuario);

        if (empty($usuarioLogado)) {
            (new Response(
                success: false,
                message: 'Usuário e senha inválidos',
                httpCode: 401
            ))->send();
            exit();
        }

        $claims = new stdClass();
        $claims->name = $usuarioLogado[0]->getNomeUsuario();
        $claims->email = $usuarioLogado[0]->getEmail();
        $claims->role = $usuarioLogado[0]->getAplicativo()->getNomeAplicativo();
        $claims->idUsuario = $usuarioLogado[0]->getIdUsuario();
        $claims->idAplicativo = $usuarioLogado[0]->getAplicativo()->getIdAplicativo();

        $meuToken = new MeuTokenJWT();
        $token = $meuToken->gerarToken($claims);

        (new Response(
            success: true,
            message: 'Usuário autenticado com sucesso',
            data: [
                'token' => $token,
                'usuario' => $usuarioLogado[0]
            ],
            httpCode: 200
        ))->send();
        exit();
    }
}
