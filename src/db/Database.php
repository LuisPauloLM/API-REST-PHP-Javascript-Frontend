<?php

declare(strict_types=1);

require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/Logger.php';

class Database
{
  private const HOST = '127.0.0.1';
  private const USER = 'root';
  private const PASSWORD = 'Henry45*1';
  private const DATABASE = 'aula_api_2024';
  private const PORT = 3306;
  private const CHARACTER_SET = 'utf8mb4';

  private static ?PDO $CONNECTION = null;

  public static function getConnection(): PDO
  {
    if (self::$CONNECTION === null) {
      self::connect();
    }
    return self::$CONNECTION;
  }

  private static function connect(): void
  {
    try {
      $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        self::HOST,
        self::PORT,
        self::DATABASE,
        self::CHARACTER_SET
      );

      self::$CONNECTION = new PDO(
        $dsn,
        self::USER,
        self::PASSWORD,
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
          PDO::ATTR_EMULATE_PREPARES => false
        ]
      );
    } catch (PDOException $e) {
      if ($e->getCode() === 1049) {
        self::initializeDatabase();
        self::connect();
      } else {
        Logger::log($e);
        (new Response(
          false,
          'Erro ao conectar ao banco de dados.',
          [
            'code' => $e->getCode(),
            'message' => $e->getMessage()
          ],

        ))->send();
        exit();
      }
    }
  }

  private static function initializeDatabase(): void
  {
    try {
      $dsn = sprintf(
        'mysql:host=%s;port=%d;charset=%s',
        self::HOST,
        self::PORT,
        self::CHARACTER_SET
      );

      $tempPdo = new PDO(
        $dsn,
        self::USER,
        self::PASSWORD,
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
      );

      $tempPdo->exec("CREATE DATABASE IF NOT EXISTS " . self::DATABASE . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
      $tempPdo->exec("USE " . self::DATABASE);

      self::createTables($tempPdo);
      self::seedInitialData($tempPdo);

      Logger::log(new Exception("Banco de dados inicializado automaticamente"));
    } catch (PDOException $e) {
      Logger::log($e);
      (new Response(
        false,
        'Erro ao inicializar banco de dados.',
        [
          'code' => $e->getCode(),
          'message' => $e->getMessage()
        ],

      ))->send();
      exit();
    }
  }

  private static function createTables(PDO $pdo): void
  {
    $tables = [
      "CREATE TABLE IF NOT EXISTS aplicativo (
                idAplicativo INT PRIMARY KEY AUTO_INCREMENT,
                nomeAplicativo VARCHAR(45) NOT NULL UNIQUE,
                nomeEmpresa VARCHAR(45),
                dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                dataAtualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",

      "CREATE TABLE IF NOT EXISTS veiculo (
                idVeiculo INT PRIMARY KEY AUTO_INCREMENT,
                nomeVeiculo VARCHAR(45) NOT NULL,
                usado TINYINT(1) DEFAULT 1,
                aplicativo_idAplicativo INT NOT NULL,
                dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                dataAtualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (aplicativo_idAplicativo) 
                    REFERENCES aplicativo(idAplicativo) 
                    ON DELETE CASCADE
            ) ENGINE=InnoDB",

      "CREATE TABLE IF NOT EXISTS usuario (
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
            ) ENGINE=InnoDB"
    ];

    foreach ($tables as $tableSql) {
      try {
        $pdo->exec($tableSql);
      } catch (PDOException $e) {
        Logger::log($e);
      }
    }
  }

  private static function seedInitialData(PDO $pdo): void
  {
    $senhaHash = password_hash('Senha123!', PASSWORD_DEFAULT);

    $aplicativos = [
      "INSERT IGNORE INTO aplicativo (idAplicativo, nomeAplicativo, nomeEmpresa) VALUES 
            (1, 'Uber', 'Uber Technologies'),
            (2, '99', '99 Pop'),
            (3, 'Cabify', 'Cabify Spain'),
            (4, 'Lyft', 'Lyft Inc.'),
            (5, 'Grab', 'Grab Holdings')"
    ];

    $veiculos = [
      "INSERT IGNORE INTO veiculo (idVeiculo, nomeVeiculo, usado, aplicativo_idAplicativo) VALUES 
            (1, 'Toyota Corolla', 1, 1),
            (2, 'Honda Civic', 0, 1),
            (3, 'Ford Fusion', 1, 2),
            (4, 'Chevrolet Onix', 1, 3),
            (5, 'Volkswagen Golf', 0, 4)"
    ];

    $usuarios = [
      "INSERT IGNORE INTO usuario (idUsuario, nomeUsuario, email, senha, tipo, maiorIdade, aplicativo_idAplicativo) VALUES 
            (1, 'João Silva', 'joao@email.com', '$senhaHash', 'admin', 1, 1),
            (2, 'Maria Souza', 'maria@email.com', '$senhaHash', 'user', 1, 2),
            (3, 'Carlos Lima', 'carlos@email.com', '$senhaHash', 'user', 1, 3),
            (4, 'Ana Paula', 'ana@email.com', '$senhaHash', 'admin', 1, 4),
            (5, 'Pedro Rocha', 'pedro@email.com', '$senhaHash', 'user', 1, 5)"
    ];

    $allData = array_merge($aplicativos, $veiculos, $usuarios);

    foreach ($allData as $dataSql) {
      try {
        $pdo->exec($dataSql);
      } catch (PDOException $e) {
        if ($e->getCode() !== 23000) {
          Logger::log($e);
        }
      }
    }
  }

  public static function resetDatabase(): void
  {
    if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
      (new Response(
        false,
        'Reset de banco não permitido em produção',
        [],

      ))->send();
      exit();
    }

    try {
      $dsn = sprintf(
        'mysql:host=%s;port=%d;charset=%s',
        self::HOST,
        self::PORT,
        self::CHARACTER_SET
      );

      $tempPdo = new PDO($dsn, self::USER, self::PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);

      $tempPdo->exec("DROP DATABASE IF EXISTS " . self::DATABASE);
      self::initializeDatabase();

      (new Response(
        true,
        '✅ Banco de dados resetado com sucesso!',
        [],

      ))->send();
    } catch (PDOException $e) {
      Logger::log($e);
      (new Response(
        false,
        '❌ Erro ao resetar banco de dados.',
        ['message' => $e->getMessage()],

      ))->send();
    }
    exit();
  }

  public static function backup(string $backupPath = null): string
  {
    $backupPath = $backupPath ?? __DIR__ . '/../backups/backup_' . date('Y-m-d_H-i-s') . '.sql';

    if (!is_dir(dirname($backupPath))) {
      mkdir(dirname($backupPath), 0755, true);
    }

    try {
      $command = sprintf(
        'mysqldump --host=%s --port=%d --user=%s --password=%s %s > %s',
        self::HOST,
        self::PORT,
        self::USER,
        self::PASSWORD,
        self::DATABASE,
        $backupPath
      );

      exec($command, $output, $returnCode);

      if ($returnCode !== 0) {
        throw new Exception('Erro ao executar mysqldump. Código: ' . $returnCode);
      }

      return $backupPath;
    } catch (Exception $e) {
      Logger::log($e);
      throw new Exception('Erro ao criar backup: ' . $e->getMessage());
    }
  }

  public static function restore(string $backupPath): void
  {
    if (!file_exists($backupPath)) {
      throw new Exception('Arquivo de backup não encontrado: ' . $backupPath);
    }

    try {
      $command = sprintf(
        'mysql --host=%s --port=%d --user=%s --password=%s %s < %s',
        self::HOST,
        self::PORT,
        self::USER,
        self::PASSWORD,
        self::DATABASE,
        $backupPath
      );

      exec($command, $output, $returnCode);

      if ($returnCode !== 0) {
        throw new Exception('Erro ao restaurar backup. Código: ' . $returnCode);
      }
    } catch (Exception $e) {
      Logger::log($e);
      throw new Exception('Erro ao restaurar backup: ' . $e->getMessage());
    }
  }

  public static function checkConnection(): bool
  {
    try {
      self::getConnection()->query('SELECT 1');
      return true;
    } catch (PDOException $e) {
      return false;
    }
  }

  public static function getStats(): array
  {
    $pdo = self::getConnection();

    $stats = [
      'aplicativos' => $pdo->query('SELECT COUNT(*) as count FROM aplicativo')->fetch()->count,
      'usuarios' => $pdo->query('SELECT COUNT(*) as count FROM usuario')->fetch()->count,
      'veiculos' => $pdo->query('SELECT COUNT(*) as count FROM veiculo')->fetch()->count,
      'tables' => []
    ];

    $tables = $pdo->query('SHOW TABLES')->fetchAll();
    foreach ($tables as $table) {
      $tableName = $table->{'Tables_in_' . self::DATABASE};
      $stats['tables'][$tableName] = $pdo->query("SELECT COUNT(*) as count FROM $tableName")->fetch()->count;
    }

    return $stats;
  }

  public static function transaction(callable $callback): mixed
  {
    $pdo = self::getConnection();

    try {
      $pdo->beginTransaction();
      $result = $callback($pdo);
      $pdo->commit();
      return $result;
    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
