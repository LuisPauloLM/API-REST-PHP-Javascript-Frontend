<?php

declare(strict_types=1);

require_once __DIR__ . '/Aplicativo.php';

class Veiculo implements JsonSerializable
{
    public function __construct(
        private ?int $idVeiculo = null,
        private string $nomeVeiculo = "",
        private int $usado = 0, // 0 ou 1
        private Aplicativo $aplicativo = new Aplicativo()
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'idVeiculo' => $this->idVeiculo,
            'nomeVeiculo' => $this->nomeVeiculo,
            'usado' => $this->usado,
            'aplicativo' => $this->aplicativo->jsonSerialize()
        ];
    }

    public function getIdVeiculo(): ?int
    {
        return $this->idVeiculo;
    }

    public function setIdVeiculo(?int $idVeiculo): self
    {
        $this->idVeiculo = $idVeiculo;
        return $this;
    }

    public function getNomeVeiculo(): string
    {
        return $this->nomeVeiculo;
    }

    public function setNomeVeiculo(string $nomeVeiculo): self
    {
        $this->nomeVeiculo = $nomeVeiculo;
        return $this;
    }

    public function getUsado(): int
    {
        return $this->usado;
    }

    public function setUsado(int $usado): self
    {
        $this->usado = $usado;
        return $this;
    }

    public function getAplicativo(): Aplicativo
    {
        return $this->aplicativo;
    }

    public function setAplicativo(Aplicativo $aplicativo): self
    {
        $this->aplicativo = $aplicativo;
        return $this;
    }
}
