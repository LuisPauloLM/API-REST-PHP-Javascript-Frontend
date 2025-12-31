<?php

declare(strict_types=1);

class Aplicativo implements JsonSerializable
{
    public function __construct(
        private ?int $idAplicativo = null,
        private string $nomeAplicativo = "",
        private ?string $nomeEmpresa = null
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'idAplicativo' => $this->idAplicativo,
            'nomeAplicativo' => $this->nomeAplicativo,
            'nomeEmpresa' => $this->nomeEmpresa
        ];
    }

    public function getIdAplicativo(): ?int
    {
        return $this->idAplicativo;
    }

    public function setIdAplicativo(?int $idAplicativo): self
    {
        $this->idAplicativo = $idAplicativo;
        return $this;
    }

    public function getNomeAplicativo(): string
    {
        return $this->nomeAplicativo;
    }

    public function setNomeAplicativo(string $nomeAplicativo): self
    {
        $this->nomeAplicativo = $nomeAplicativo;
        return $this;
    }

    public function getNomeEmpresa(): ?string
    {
        return $this->nomeEmpresa;
    }

    public function setNomeEmpresa(?string $nomeEmpresa): self
    {
        $this->nomeEmpresa = $nomeEmpresa;
        return $this;
    }
}
