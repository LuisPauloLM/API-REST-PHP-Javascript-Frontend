<?php

declare(strict_types=1);

// Importa as classes necessárias
require_once __DIR__ . '/../controllers/AplicativoControl.php';
require_once __DIR__ . '/../controllers/UsuarioControl.php';
require_once __DIR__ . '/../controllers/VeiculoControl.php';
require_once __DIR__ . '/../controllers/LoginControl.php';
require_once __DIR__ . '/../middlewares/AplicativoMiddleware.php';
require_once __DIR__ . '/../middlewares/UsuarioMiddleware.php';
require_once __DIR__ . '/../middlewares/VeiculoMiddleware.php';
require_once __DIR__ . '/../middlewares/LoginMiddleware.php';
require_once __DIR__ . '/../middlewares/JWTMiddleware.php';
require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../utils/MeuTokenJWT.php';

// Importa a classe Router do Bramus
require_once(__DIR__ . '/Router.php');

class Roteador
{
    private Router $router; // Instância do Router

    public function __construct()
    {
        $this->router = new Router(); // Inicializa o Router
        $this->setupHeaders(); // Configura os cabeçalhos HTTP
        $this->setupRoutes(); // Configura as rotas
        $this->setup404Route(); // Configura a rota 404
    }

    // Configura os cabeçalhos HTTP
    private function setupHeaders(): void
    {
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Trata requisições OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    // Envia uma resposta de erro
    private function sendErrorResponse(Throwable $throwable, string $message, $httpCode = 500): never
    {
        Logger::log(throwable: $throwable);

        // Garante que httpCode seja sempre um inteiro
        $httpCodeInt = is_int($httpCode) ? $httpCode : 500;

        // Se for um erro PDO, converte códigos SQL para HTTP
        if ($throwable instanceof PDOException) {
            $httpCodeInt = 500; // Erros de banco sempre retornam 500
        }

        $response = new Response(
            success: false,
            message: $message,
            error: [
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage()
            ],
            httpCode: $httpCodeInt
        );
        $response->send();
        exit();
    }

    // Configura a rota 404
    private function setup404Route(): void
    {
        $this->router->set404(function (): void {
            $response = new Response(
                success: false,
                message: "Rota não encontrada",
                error: [
                    'code' => 'routing_error',
                    'message' => 'A rota solicitada não foi mapeada.'
                ],
                httpCode: 404
            );
            $response->send(); // Envia resposta 404
        });
    }

    // Configura as rotas da API
    private function setupRoutes(): void
    {
        // 🔄 ROTA PARA RESETAR O BANCO (Útil para desenvolvimento)
        $this->router->post('/reset-database', function (): void {
            try {
                Database::resetDatabase();
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao resetar banco de dados.');
            }
        });

        // 🔄 ROTA alternativa via GET (para facilitar no navegador)
        $this->router->get('/reset-database', function (): void {
            try {
                Database::resetDatabase();
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao resetar banco de dados.');
            }
        });

        // 🔐 ROTA DE LOGIN (pública - não precisa de autenticação)
        $this->router->post('/login', function (): void {
            try {
                // Captura o corpo da requisição
                $requestBody = file_get_contents('php://input');

                // Verifica se o corpo está vazio
                if (empty($requestBody)) {
                    throw new Exception('Dados de login não fornecidos', 400);
                }

                // Converte JSON para objeto
                $middleware = new LoginMiddleware();
                $stdLogin = $middleware->stringJsonToStdClass($requestBody);

                // Valida email e senha
                $middleware->isValidEmail($stdLogin->usuario->email)
                    ->isValidSenha($stdLogin->usuario->senha);

                // Autentica o usuário
                $loginControl = new LoginControl();
                $loginControl->autenticar($stdLogin);
            } catch (Exception $e) {
                $httpCode = is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500;

                $response = new Response(
                    success: false,
                    message: 'Erro ao processar login',
                    error: [
                        'code' => 'login_error',
                        'message' => $e->getMessage()
                    ],
                    httpCode: $httpCode
                );
                $response->send();
            }
        });

        // 🎯 ROTAS PARA APLICATIVOS (protegidas por JWT)
        $this->router->get('/aplicativos', function (): void {
            try {
                // Valida token JWT antes de acessar a rota
                (new JWTMiddleware())->isValidToken();
                (new AplicativoControl())->index();
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao listar aplicativos.');
            }
        });

        $this->router->get('/aplicativos/(\d+)', function (int $idAplicativo): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new AplicativoMiddleware())->isValidId($idAplicativo)->hasAplicativoById($idAplicativo);
                (new AplicativoControl())->show($idAplicativo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao buscar aplicativo.', $e->getCode() ?: 500);
            }
        });

        $this->router->post('/aplicativos', function (): void {
            try {
                (new JWTMiddleware())->isValidToken();
                $requestBody = file_get_contents('php://input');
                $middleware = new AplicativoMiddleware();
                $stdAplicativo = $middleware->stringJsonToStdClass($requestBody);
                $middleware->isValidNomeAplicativo($stdAplicativo->aplicativo->nomeAplicativo)
                    ->hasNotAplicativoByName($stdAplicativo->aplicativo->nomeAplicativo);
                (new AplicativoControl())->store($stdAplicativo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao cadastrar aplicativo.', $e->getCode() ?: 500);
            }
        });

        $this->router->put('/aplicativos/(\d+)', function (int $idAplicativo): void {
            try {
                (new JWTMiddleware())->isValidToken();
                $requestBody = file_get_contents('php://input');
                $middleware = new AplicativoMiddleware();
                $stdAplicativo = $middleware->stringJsonToStdClass($requestBody);
                $middleware->isValidId($idAplicativo)
                    ->hasAplicativoById($idAplicativo)
                    ->isValidNomeAplicativo($stdAplicativo->aplicativo->nomeAplicativo);

                $existingApp = (new AplicativoDAO())->readByName($stdAplicativo->aplicativo->nomeAplicativo);
                if ($existingApp && $existingApp->getIdAplicativo() !== $idAplicativo) {
                    $response = new Response(
                        success: false,
                        message: 'Nome de aplicativo já em uso por outro registro.',
                        error: ['code' => 'validation_error', 'message' => 'O nome fornecido já pertence a outro aplicativo.'],
                        httpCode: 409
                    );
                    $response->send();
                    exit();
                }
                (new AplicativoControl())->edit($stdAplicativo, $idAplicativo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao atualizar aplicativo.', $e->getCode() ?: 500);
            }
        });

        $this->router->delete('/aplicativos/(\d+)', function (int $idAplicativo): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new AplicativoMiddleware())->isValidId($idAplicativo)->hasAplicativoById($idAplicativo);
                (new AplicativoControl())->destroy($idAplicativo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao excluir aplicativo.', $e->getCode() ?: 500);
            }
        });

        // 👥 ROTAS PARA USUÁRIOS (protegidas por JWT)
        $this->router->get('/usuarios', function (): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new UsuarioControl())->index();
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao listar usuários.');
            }
        });

        $this->router->get('/usuarios/(\d+)', function (int $idUsuario): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new UsuarioMiddleware())->isValidId($idUsuario)->hasUsuarioById($idUsuario);
                (new UsuarioControl())->show($idUsuario);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao buscar usuário.', $e->getCode() ?: 500);
            }
        });

        $this->router->post('/usuarios', function (): void {
            try {
                (new JWTMiddleware())->isValidToken();
                $requestBody = file_get_contents('php://input');
                $middleware = new UsuarioMiddleware();
                $stdUsuario = $middleware->stringJsonToStdClass($requestBody);
                $middleware->isValidNomeUsuario($stdUsuario->usuario->nomeUsuario)
                    ->isValidEmail($stdUsuario->usuario->email)
                    ->hasNotUsuarioByEmail($stdUsuario->usuario->email)
                    ->isValidSenha($stdUsuario->usuario->senha)
                    ->isValidMaiorIdade($stdUsuario->usuario->maiorIdade)
                    ->hasAplicativoById($stdUsuario->usuario->aplicativo_idAplicativo);
                (new UsuarioControl())->store($stdUsuario);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao cadastrar usuário.', $e->getCode() ?: 500);
            }
        });

        $this->router->put('/usuarios/(\d+)', function (int $idUsuario): void {
            try {
                (new JWTMiddleware())->isValidToken();
                $requestBody = file_get_contents('php://input');
                $middleware = new UsuarioMiddleware();
                $stdUsuario = $middleware->stringJsonToStdClass($requestBody);
                $middleware->isValidId($idUsuario)
                    ->hasUsuarioById($idUsuario)
                    ->isValidNomeUsuario($stdUsuario->usuario->nomeUsuario)
                    ->isValidEmail($stdUsuario->usuario->email);

                $existingUser = (new UsuarioDAO())->readByEmail($stdUsuario->usuario->email);
                if ($existingUser && $existingUser->getIdUsuario() !== $idUsuario) {
                    $response = new Response(
                        success: false,
                        message: 'Email já em uso por outro usuário.',
                        error: ['code' => 'validation_error', 'message' => 'O email fornecido já pertence a outro usuário.'],
                        httpCode: 409
                    );
                    $response->send();
                    exit();
                }

                $middleware->isValidMaiorIdade($stdUsuario->usuario->maiorIdade)
                    ->hasAplicativoById($stdUsuario->usuario->aplicativo_idAplicativo);

                if (isset($stdUsuario->usuario->senha) && !empty($stdUsuario->usuario->senha)) {
                    $middleware->isValidSenha($stdUsuario->usuario->senha);
                }

                (new UsuarioControl())->edit($stdUsuario, $idUsuario);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao atualizar usuário.', $e->getCode() ?: 500);
            }
        });

        $this->router->delete('/usuarios/(\d+)', function (int $idUsuario): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new UsuarioMiddleware())->isValidId($idUsuario)->hasUsuarioById($idUsuario);
                (new UsuarioControl())->destroy($idUsuario);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao excluir usuário.', $e->getCode() ?: 500);
            }
        });

        // 🚗 ROTAS PARA VEÍCULOS (protegidas por JWT)
        $this->router->get('/veiculos', function (): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new VeiculoControl())->index();
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao listar veículos.');
            }
        });

        $this->router->get('/veiculos/(\d+)', function (int $idVeiculo): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new VeiculoMiddleware())->isValidId($idVeiculo)->hasVeiculoById($idVeiculo);
                (new VeiculoControl())->show($idVeiculo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao buscar veículo.', $e->getCode() ?: 500);
            }
        });

        $this->router->post('/veiculos', function (): void {
            try {
                (new JWTMiddleware())->isValidToken();
                $requestBody = file_get_contents('php://input');
                $middleware = new VeiculoMiddleware();
                $stdVeiculo = $middleware->stringJsonToStdClass($requestBody);
                $middleware->isValidNomeVeiculo($stdVeiculo->veiculo->nomeVeiculo)
                    ->isValidUsado($stdVeiculo->veiculo->usado)
                    ->hasAplicativoById($stdVeiculo->veiculo->aplicativo_idAplicativo);
                (new VeiculoControl())->store($stdVeiculo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao cadastrar veículo.', $e->getCode() ?: 500);
            }
        });

        $this->router->put('/veiculos/(\d+)', function (int $idVeiculo): void {
            try {
                (new JWTMiddleware())->isValidToken();
                $requestBody = file_get_contents('php://input');
                $middleware = new VeiculoMiddleware();
                $stdVeiculo = $middleware->stringJsonToStdClass($requestBody);
                $middleware->isValidId($idVeiculo)
                    ->hasVeiculoById($idVeiculo)
                    ->isValidNomeVeiculo($stdVeiculo->veiculo->nomeVeiculo)
                    ->isValidUsado($stdVeiculo->veiculo->usado)
                    ->hasAplicativoById($stdVeiculo->veiculo->aplicativo_idAplicativo);
                (new VeiculoControl())->edit($stdVeiculo, $idVeiculo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao atualizar veículo.', $e->getCode() ?: 500);
            }
        });

        $this->router->delete('/veiculos/(\d+)', function (int $idVeiculo): void {
            try {
                (new JWTMiddleware())->isValidToken();
                (new VeiculoMiddleware())->isValidId($idVeiculo)->hasVeiculoById($idVeiculo);
                (new VeiculoControl())->destroy($idVeiculo);
            } catch (Throwable $e) {
                $this->sendErrorResponse($e, 'Erro ao excluir veículo.', $e->getCode() ?: 500);
            }
        });
    }

    // Inicia o roteador
    public function start(): void
    {
        $this->router->run();
    }
}
