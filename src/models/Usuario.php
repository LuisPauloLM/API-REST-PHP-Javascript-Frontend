<?php

declare(strict_types=1);

require_once __DIR__ . '/Aplicativo.php';

class Usuario implements JsonSerializable
{
    public function __construct(
        private ?int $idUsuario = null,
        private string $nomeUsuario = "",
        private string $email = "",
        private string $senha = "",
        private int $maiorIdade = 0, // 0 ou 1
        private string $tipo = "usuario", // Campo tipo adicionado
        private int $aplicativo_idAplicativo = 0, // Campo para compatibilidade
        private Aplicativo $aplicativo = new Aplicativo()
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'idUsuario' => $this->idUsuario,
            'nomeUsuario' => $this->nomeUsuario,
            'email' => $this->email,
            // Senha nunca exposta em JSON - removido completamente
            'maiorIdade' => $this->maiorIdade,
            'tipo' => $this->tipo,
            'aplicativo_idAplicativo' => $this->aplicativo_idAplicativo,
            'aplicativo' => $this->aplicativo->jsonSerialize()
        ];
    }

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(?int $idUsuario): self
    {
        $this->idUsuario = $idUsuario;
        return $this;
    }

    public function getNomeUsuario(): string
    {
        return $this->nomeUsuario;
    }

    public function setNomeUsuario(string $nomeUsuario): self
    {
        $this->nomeUsuario = $nomeUsuario;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // MÉTODO REMOVIDO POR SEGURANÇA - senha não deve ser acessível via getter
    // public function getSenha(): string
    // {
    //     return $this->senha;
    // }

    // Método interno para uso apenas pelo sistema (DAO, etc.)
    public function getSenhaHash(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): self
    {
        $this->senha = $senha;
        return $this;
    }

    public function getMaiorIdade(): int
    {
        return $this->maiorIdade;
    }

    public function setMaiorIdade(int $maiorIdade): self
    {
        $this->maiorIdade = $maiorIdade;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getAplicativoIdAplicativo(): int
    {
        return $this->aplicativo_idAplicativo;
    }

    public function setAplicativoIdAplicativo(int $aplicativo_idAplicativo): self
    {
        $this->aplicativo_idAplicativo = $aplicativo_idAplicativo;
        return $this;
    }

    public function getAplicativo(): Aplicativo
    {
        return $this->aplicativo;
    }

    public function setAplicativo(Aplicativo $aplicativo): self
    {
        $this->aplicativo = $aplicativo;
        // Sincroniza o ID do aplicativo
        $this->aplicativo_idAplicativo = $aplicativo->getIdAplicativo() ?? 0;
        return $this;
    }

    // Método para verificar senha (sem expor o hash)
    public function verificarSenha(string $senhaPlana): bool
    {
        return password_verify($senhaPlana, $this->senha);
    }
}
