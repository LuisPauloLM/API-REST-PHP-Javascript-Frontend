<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../utils/Logger.php';

class AplicativoDAO
{
    public function create(Aplicativo $aplicativo): Aplicativo
    {
        $idAplicativo = $aplicativo->getIdAplicativo();
        if (isset($idAplicativo)) {
            return $this->createWithId($aplicativo);
        } else {
            return $this->createWithoutId($aplicativo);
        }
    }

    private function createWithoutId(Aplicativo $aplicativo): Aplicativo
    {
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'INSERT INTO aplicativo (nomeAplicativo, nomeEmpresa) VALUES (:nomeAplicativo, :nomeEmpresa)';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeAplicativo', $aplicativo->getNomeAplicativo(), PDO::PARAM_STR);
        $statement->bindValue(':nomeEmpresa', $aplicativo->getNomeEmpresa(), PDO::PARAM_STR);
        $statement->execute();
        $aplicativo->setIdAplicativo((int) Database::getConnection()->lastInsertId());
        return $aplicativo;
    }

    private function createWithId(Aplicativo $aplicativo): Aplicativo
    {
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'INSERT INTO aplicativo (idAplicativo, nomeAplicativo, nomeEmpresa) VALUES (:idAplicativo, :nomeAplicativo, :nomeEmpresa)';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idAplicativo', $aplicativo->getIdAplicativo(), PDO::PARAM_INT);
        $statement->bindValue(':nomeAplicativo', $aplicativo->getNomeAplicativo(), PDO::PARAM_STR);
        $statement->bindValue(':nomeEmpresa', $aplicativo->getNomeEmpresa(), PDO::PARAM_STR);
        $statement->execute();
        return $aplicativo;
    }

    public function delete(int $idAplicativo): bool
    {
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'DELETE FROM aplicativo WHERE idAplicativo = :idAplicativo';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idAplicativo', $idAplicativo, PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }

    public function readAll(): array
    {
        $resultados = [];
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'SELECT idAplicativo, nomeAplicativo, nomeEmpresa FROM aplicativo ORDER BY nomeAplicativo ASC';
        $statement = Database::getConnection()->query($query);
        while ($linha = $statement->fetch(PDO::FETCH_OBJ)) {
            $aplicativo = (new Aplicativo())
                ->setIdAplicativo($linha->idAplicativo)
                ->setNomeAplicativo($linha->nomeAplicativo)
                ->setNomeEmpresa($linha->nomeEmpresa);
            $resultados[] = $aplicativo;
        }
        return $resultados;
    }

    public function readByName(string $nomeAplicativo): ?Aplicativo
    {
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'SELECT idAplicativo, nomeAplicativo, nomeEmpresa FROM aplicativo WHERE nomeAplicativo = :nomeAplicativo';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeAplicativo', $nomeAplicativo, PDO::PARAM_STR);
        $statement->execute();
        $objStdAplicativo = $statement->fetch(PDO::FETCH_OBJ);
        if (!$objStdAplicativo) {
            return null;
        }
        return (new Aplicativo())
            ->setIdAplicativo($objStdAplicativo->idAplicativo)
            ->setNomeAplicativo($objStdAplicativo->nomeAplicativo)
            ->setNomeEmpresa($objStdAplicativo->nomeEmpresa);
    }

    public function readById(int $idAplicativo): ?Aplicativo
    {
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'SELECT idAplicativo, nomeAplicativo, nomeEmpresa FROM aplicativo WHERE idAplicativo = :idAplicativo;';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idAplicativo', $idAplicativo, PDO::PARAM_INT);
        $statement->execute();
        $linha = $statement->fetch(PDO::FETCH_OBJ);
        if (!$linha) {
            return null;
        }
        return (new Aplicativo())
            ->setIdAplicativo($linha->idAplicativo)
            ->setNomeAplicativo($linha->nomeAplicativo)
            ->setNomeEmpresa($linha->nomeEmpresa);
    }

    public function update(Aplicativo $aplicativo): bool
    {
        // CORREÇÃO: Tabela em minúsculo "aplicativo" em vez de "Aplicativo"
        $query = 'UPDATE aplicativo SET nomeAplicativo = :nomeAplicativo, nomeEmpresa = :nomeEmpresa WHERE idAplicativo = :idAplicativo';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeAplicativo', $aplicativo->getNomeAplicativo(), PDO::PARAM_STR);
        $statement->bindValue(':nomeEmpresa', $aplicativo->getNomeEmpresa(), PDO::PARAM_STR);
        $statement->bindValue(':idAplicativo', $aplicativo->getIdAplicativo(), PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }
}
