-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS aula_api_2024 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE aula_api_2024;

-- Tabela aplicativo
CREATE TABLE IF NOT EXISTS aplicativo (
    idAplicativo INT PRIMARY KEY AUTO_INCREMENT,
    nomeAplicativo VARCHAR(45) NOT NULL UNIQUE,
    nomeEmpresa VARCHAR(45),
    dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dataAtualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela veiculo
CREATE TABLE IF NOT EXISTS veiculo (
    idVeiculo INT PRIMARY KEY AUTO_INCREMENT,
    nomeVeiculo VARCHAR(45) NOT NULL,
    usado TINYINT(1) DEFAULT 1,
    aplicativo_idAplicativo INT NOT NULL,
    dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dataAtualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aplicativo_idAplicativo) 
        REFERENCES aplicativo(idAplicativo) 
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela usuario
CREATE TABLE IF NOT EXISTS usuario (
    idUsuario INT PRIMARY KEY AUTO_INCREMENT,
    nomeUsuario VARCHAR(128) NOT NULL,
    email VARCHAR(64) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'user') DEFAULT 'user',
    maiorIdade TINYINT(1) DEFAULT 1,
    aplicativo_idAplicativo INT NOT NULL,
    dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dataAtualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aplicativo_idAplicativo) 
        REFERENCES aplicativo(idAplicativo) 
        ON DELETE CASCADE
) ENGINE=InnoDB;