<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Veiculo.php';
require_once __DIR__ . '/../models/Aplicativo.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../utils/Logger.php';

class VeiculoDAO
{
    public function create(Veiculo $veiculo): Veiculo
    {
        $idVeiculo = $veiculo->getIdVeiculo();
        if (isset($idVeiculo)) {
            return $this->createWithId(veiculo: $veiculo);
        } else {
            return $this->createWithoutId(veiculo: $veiculo);
        }
    }

    private function createWithoutId(Veiculo $veiculo): Veiculo
    {
        $query = 'INSERT INTO veiculo (nomeVeiculo, usado, aplicativo_idAplicativo) VALUES (:nomeVeiculo, :usado, :aplicativo_idAplicativo)';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeVeiculo', $veiculo->getNomeVeiculo(), PDO::PARAM_STR);
        $statement->bindValue(':usado', $veiculo->getUsado(), PDO::PARAM_INT);
        $statement->bindValue(':aplicativo_idAplicativo', $veiculo->getAplicativo()->getIdAplicativo(), PDO::PARAM_INT);
        $statement->execute();
        $veiculo->setIdVeiculo((int) Database::getConnection()->lastInsertId());
        return $veiculo;
    }

    private function createWithId(Veiculo $veiculo): Veiculo
    {
        $query = 'INSERT INTO veiculo (idVeiculo, nomeVeiculo, usado, aplicativo_idAplicativo) VALUES (:idVeiculo, :nomeVeiculo, :usado, :aplicativo_idAplicativo)';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idVeiculo', $veiculo->getIdVeiculo(), PDO::PARAM_INT);
        $statement->bindValue(':nomeVeiculo', $veiculo->getNomeVeiculo(), PDO::PARAM_STR);
        $statement->bindValue(':usado', $veiculo->getUsado(), PDO::PARAM_INT);
        $statement->bindValue(':aplicativo_idAplicativo', $veiculo->getAplicativo()->getIdAplicativo(), PDO::PARAM_INT);
        $statement->execute();
        return $veiculo;
    }

    public function delete(int $idVeiculo): bool
    {
        $query = 'DELETE FROM veiculo WHERE idVeiculo = :idVeiculo';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idVeiculo', $idVeiculo, PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }

    public function readAll(): array
    {
        $resultados = [];
        $query = '
            SELECT 
                v.idVeiculo, v.nomeVeiculo, v.usado,
                a.idAplicativo, a.nomeAplicativo, a.nomeEmpresa
            FROM veiculo v
            JOIN aplicativo a ON v.aplicativo_idAplicativo = a.idAplicativo
            ORDER BY v.nomeVeiculo ASC
        ';
        $statement = Database::getConnection()->query($query);
        while ($linha = $statement->fetch(mode: PDO::FETCH_OBJ)) {
            $aplicativo = (new Aplicativo())
                ->setIdAplicativo($linha->idAplicativo)
                ->setNomeAplicativo($linha->nomeAplicativo)
                ->setNomeEmpresa($linha->nomeEmpresa);

            $veiculo = (new Veiculo())
                ->setIdVeiculo($linha->idVeiculo)
                ->setNomeVeiculo($linha->nomeVeiculo)
                ->setUsado($linha->usado)
                ->setAplicativo($aplicativo);
            $resultados[] = $veiculo;
        }
        return $resultados;
    }

    public function readById(int $idVeiculo): ?Veiculo
    {
        $query = '
            SELECT 
                v.idVeiculo, v.nomeVeiculo, v.usado,
                a.idAplicativo, a.nomeAplicativo, a.nomeEmpresa
            FROM veiculo v
            JOIN aplicativo a ON v.aplicativo_idAplicativo = a.idAplicativo
            WHERE v.idVeiculo = :idVeiculo
        ';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':idVeiculo', $idVeiculo, PDO::PARAM_INT);
        $statement->execute();
        $linha = $statement->fetch(mode: PDO::FETCH_OBJ);
        if (!$linha) {
            return null;
        }
        $aplicativo = (new Aplicativo())
            ->setIdAplicativo($linha->idAplicativo)
            ->setNomeAplicativo($linha->nomeAplicativo)
            ->setNomeEmpresa($linha->nomeEmpresa);

        return (new Veiculo())
            ->setIdVeiculo($linha->idVeiculo)
            ->setNomeVeiculo($linha->nomeVeiculo)
            ->setUsado($linha->usado)
            ->setAplicativo($aplicativo);
    }

    public function update(Veiculo $veiculo): bool
    {
        $query = 'UPDATE veiculo SET nomeVeiculo = :nomeVeiculo, usado = :usado, aplicativo_idAplicativo = :aplicativo_idAplicativo WHERE idVeiculo = :idVeiculo';
        $statement = Database::getConnection()->prepare($query);
        $statement->bindValue(':nomeVeiculo', $veiculo->getNomeVeiculo(), PDO::PARAM_STR);
        $statement->bindValue(':usado', $veiculo->getUsado(), PDO::PARAM_INT);
        $statement->bindValue(':aplicativo_idAplicativo', $veiculo->getAplicativo()->getIdAplicativo(), PDO::PARAM_INT);
        $statement->bindValue(':idVeiculo', $veiculo->getIdVeiculo(), PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }
}
