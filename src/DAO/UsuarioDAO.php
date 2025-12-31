<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../utils/Logger.php';

class UsuarioDAO
{
    public function create(Usuario $usuario): Usuario
    {
        $idUsuario = $usuario->getIdUsuario();
        if (isset($idUsuario)) {
            return $this->createWithId(usuario: $usuario);
        } else {
            return $this->createWithoutId(usuario: $usuario);
        }
    }

    private function createWithoutId(Usuario $usuario): Usuario
    {
        // CORRIGIDO: usa nome de tabela em minúsculo (consistente com o banco)
        $query = 'INSERT INTO usuario (nomeUsuario, email, senha, maiorIdade, tipo, aplicativo_idAplicativo) VALUES (:nomeUsuario, :email, :senha, :maiorIdade, :tipo, :aplicativo_idAplicativo)';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeUsuario', $usuario->getNomeUsuario(), PDO::PARAM_STR);
        $statement->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':senha', $usuario->getSenhaHash(), PDO::PARAM_STR); // Usa método específico para hash
        $statement->bindValue(':maiorIdade', $usuario->getMaiorIdade(), PDO::PARAM_INT);
        $statement->bindValue(':tipo', $usuario->getTipo(), PDO::PARAM_STR);
        $statement->bindValue(':aplicativo_idAplicativo', $usuario->getAplicativo()->getIdAplicativo(), PDO::PARAM_INT);
        $statement->execute();
        $usuario->setIdUsuario((int) Database::getConnection()->lastInsertId());
        return $usuario;
    }

    private function createWithId(Usuario $usuario): Usuario
    {
        // CORRIGIDO: usa nome de tabela em minúsculo (consistente com o banco)
        $query = 'INSERT INTO usuario (idUsuario, nomeUsuario, email, senha, maiorIdade, tipo, aplicativo_idAplicativo) VALUES (:idUsuario, :nomeUsuario, :email, :senha, :maiorIdade, :tipo, :aplicativo_idAplicativo)';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idUsuario', $usuario->getIdUsuario(), PDO::PARAM_INT);
        $statement->bindValue(':nomeUsuario', $usuario->getNomeUsuario(), PDO::PARAM_STR);
        $statement->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':senha', $usuario->getSenhaHash(), PDO::PARAM_STR); // Usa método específico para hash
        $statement->bindValue(':maiorIdade', $usuario->getMaiorIdade(), PDO::PARAM_INT);
        $statement->bindValue(':tipo', $usuario->getTipo(), PDO::PARAM_STR);
        $statement->bindValue(':aplicativo_idAplicativo', $usuario->getAplicativo()->getIdAplicativo(), PDO::PARAM_INT);
        $statement->execute();
        return $usuario;
    }

    public function delete(int $idUsuario): bool
    {
        // CORRIGIDO: usa nome de tabela em minúsculo (consistente com o banco)
        $query = 'DELETE FROM usuario WHERE idUsuario = :idUsuario';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }

    public function readAll(): array
    {
        $resultados = [];
        // CORRIGIDO: usa nomes de tabela em minúsculo (consistente com o banco)
        $query = '
            SELECT 
                u.idUsuario, u.nomeUsuario, u.email, u.senha, u.maiorIdade, u.tipo,
                a.idAplicativo, a.nomeAplicativo, a.nomeEmpresa
            FROM usuario u
            JOIN aplicativo a ON u.aplicativo_idAplicativo = a.idAplicativo
            ORDER BY u.nomeUsuario ASC
        ';
        $statement = Database::getConnection()->query($query);
        while ($linha = $statement->fetch(mode: PDO::FETCH_OBJ)) {
            $aplicativo = (new Aplicativo())
                ->setIdAplicativo($linha->idAplicativo)
                ->setNomeAplicativo($linha->nomeAplicativo)
                ->setNomeEmpresa($linha->nomeEmpresa);

            $usuario = (new Usuario())
                ->setIdUsuario($linha->idUsuario)
                ->setNomeUsuario($linha->nomeUsuario)
                ->setEmail($linha->email)
                ->setSenha($linha->senha) // Senha hash vem do banco
                ->setMaiorIdade($linha->maiorIdade)
                ->setTipo($linha->tipo ?? 'usuario') // Valor padrão se campo não existir
                ->setAplicativo($aplicativo);
            $resultados[] = $usuario;
        }
        return $resultados;
    }

    public function readById(int $idUsuario): ?Usuario
    {
        // CORRIGIDO: usa nomes de tabela em minúsculo (consistente com o banco)
        $query = '
            SELECT 
                u.idUsuario, u.nomeUsuario, u.email, u.senha, u.maiorIdade, u.tipo,
                a.idAplicativo, a.nomeAplicativo, a.nomeEmpresa
            FROM usuario u
            JOIN aplicativo a ON u.aplicativo_idAplicativo = a.idAplicativo
            WHERE u.idUsuario = :idUsuario
        ';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $statement->execute();
        $linha = $statement->fetch(mode: PDO::FETCH_OBJ);
        if (!$linha) {
            return null;
        }
        $aplicativo = (new Aplicativo())
            ->setIdAplicativo($linha->idAplicativo)
            ->setNomeAplicativo($linha->nomeAplicativo)
            ->setNomeEmpresa($linha->nomeEmpresa);

        return (new Usuario())
            ->setIdUsuario($linha->idUsuario)
            ->setNomeUsuario($linha->nomeUsuario)
            ->setEmail($linha->email)
            ->setSenha($linha->senha) // Senha hash vem do banco
            ->setMaiorIdade($linha->maiorIdade)
            ->setTipo($linha->tipo ?? 'usuario') // Valor padrão se campo não existir
            ->setAplicativo($aplicativo);
    }

    public function readByEmail(string $email): ?Usuario
    {
        // CORRIGIDO: usa nomes de tabela em minúsculo (consistente com o banco)
        $query = '
            SELECT 
                u.idUsuario, u.nomeUsuario, u.email, u.senha, u.maiorIdade, u.tipo,
                a.idAplicativo, a.nomeAplicativo, a.nomeEmpresa
            FROM usuario u
            JOIN aplicativo a ON u.aplicativo_idAplicativo = a.idAplicativo
            WHERE u.email = :email
        ';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->execute();
        $linha = $statement->fetch(mode: PDO::FETCH_OBJ);
        if (!$linha) {
            return null;
        }
        $aplicativo = (new Aplicativo())
            ->setIdAplicativo($linha->idAplicativo)
            ->setNomeAplicativo($linha->nomeAplicativo)
            ->setNomeEmpresa($linha->nomeEmpresa);

        return (new Usuario())
            ->setIdUsuario($linha->idUsuario)
            ->setNomeUsuario($linha->nomeUsuario)
            ->setEmail($linha->email)
            ->setSenha($linha->senha) // Senha hash vem do banco
            ->setMaiorIdade($linha->maiorIdade)
            ->setTipo($linha->tipo ?? 'usuario') // Valor padrão se campo não existir
            ->setAplicativo($aplicativo);
    }

    public function update(Usuario $usuario): bool
    {
        // CORRIGIDO: usa nome de tabela em minúsculo (consistente com o banco)
        $query = 'UPDATE usuario SET nomeUsuario = :nomeUsuario, email = :email, senha = :senha, maiorIdade = :maiorIdade, tipo = :tipo, aplicativo_idAplicativo = :aplicativo_idAplicativo WHERE idUsuario = :idUsuario';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeUsuario', $usuario->getNomeUsuario(), PDO::PARAM_STR);
        $statement->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':senha', $usuario->getSenhaHash(), PDO::PARAM_STR); // Usa método específico para hash
        $statement->bindValue(':maiorIdade', $usuario->getMaiorIdade(), PDO::PARAM_INT);
        $statement->bindValue(':tipo', $usuario->getTipo(), PDO::PARAM_STR);
        $statement->bindValue(':aplicativo_idAplicativo', $usuario->getAplicativo()->getIdAplicativo(), PDO::PARAM_INT);
        $statement->bindValue(':idUsuario', $usuario->getIdUsuario(), PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }
}
