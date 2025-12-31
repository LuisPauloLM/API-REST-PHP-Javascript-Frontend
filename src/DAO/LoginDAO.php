<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../utils/Logger.php';

class LoginDAO
{
    public function verificarLogin(Usuario $usuario): array
    {
        $query = 'SELECT 
                    u.idUsuario, 
                    u.nomeUsuario, 
                    u.email, 
                    u.senha,
                    u.maiorIdade,
                    u.tipo,
                    u.aplicativo_idAplicativo,
                    a.idAplicativo, 
                    a.nomeAplicativo,
                    a.nomeEmpresa
                 FROM usuario u
                 JOIN aplicativo a ON u.aplicativo_idAplicativo = a.idAplicativo
                 WHERE u.email = :email';

        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $statement->execute();

        $linha = $statement->fetch(PDO::FETCH_OBJ);

        if (!$linha) {
            return [];
        }


        if (!password_verify($usuario->getSenhaHash(), $linha->senha)) {
            return [];
        }

        // Cria objeto Aplicativo
        $aplicativo = (new Aplicativo())
            ->setIdAplicativo($linha->idAplicativo)
            ->setNomeAplicativo($linha->nomeAplicativo)
            ->setNomeEmpresa($linha->nomeEmpresa);

        // Cria objeto Usuario sem definir senha (por segurança)
        $usuarioEncontrado = (new Usuario())
            ->setIdUsuario($linha->idUsuario)
            ->setNomeUsuario($linha->nomeUsuario)
            ->setEmail($linha->email)
            ->setMaiorIdade($linha->maiorIdade)
            ->setTipo($linha->tipo)
            ->setAplicativoIdAplicativo($linha->aplicativo_idAplicativo)
            ->setAplicativo($aplicativo);

        return [$usuarioEncontrado];
    }

    // Método auxiliar para hash de senha (usar no registro de usuários)
    public static function hashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
}
