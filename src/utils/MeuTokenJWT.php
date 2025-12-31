<?php
require_once __DIR__ . '/jwt/JWT.php';
require_once __DIR__ . '/jwt/Key.php';
require_once __DIR__ . '/jwt/SignatureInvalidException.php';
require_once __DIR__ . '/jwt/ExpiredException.php';
require_once __DIR__ . '/jwt/BeforeValidException.php';

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;

class MeuTokenJWT
{
    // Constantes estáticas
    private const ALGORITHM = 'HS256';
    private const TYPE = 'JWT';

    public function __construct(
        private stdClass $payload = new stdClass(),
        private string $iss = 'http://localhost',
        private string $aud = 'http://localhost',
        private string $sub = 'acesso_sistema',
        private int $duration = 3600 * 24 * 30 // 30 dias
    ) {}

    // CORREÇÃO: Método para obter a chave de forma segura
    private static function getKey(): string
    {
        $key = getenv('JWT_SECRET_KEY');

        if (empty($key)) {
            // Fallback para desenvolvimento (nunca usar em produção)
            if (getenv('APP_ENV') === 'development') {
                return "x9S4q0v+V0IjvHkG20uAxaHx1ijj+q1HWjHKv+ohxp/oK+77qyXkVj/l4QYHHTF3";
            }

            throw new RuntimeException('JWT_SECRET_KEY não configurada');
        }

        return $key;
    }

    public function gerarToken(stdClass $claims): string
    {
        $objHeaders = new stdClass();
        $objHeaders->alg = self::ALGORITHM;
        $objHeaders->typ = self::TYPE;

        $objPayload = new stdClass();
        $objPayload->iss = $this->iss;
        $objPayload->aud = $this->aud;
        $objPayload->sub = $this->sub;
        $objPayload->iat = time();
        $objPayload->exp = time() + $this->duration;
        $objPayload->nbf = time();
        $objPayload->jti = bin2hex(random_bytes(16));

        // Public Claims - usando estrutura USUARIO
        $objPayload->public = new stdClass();
        $objPayload->public->name = $claims->name;
        $objPayload->public->email = $claims->email;
        $objPayload->public->role = $claims->role;

        // Private Claims - usando estrutura USUARIO
        $objPayload->private = new stdClass();
        $objPayload->private->idUsuario = $claims->idUsuario;
        $objPayload->private->idAplicativo = $claims->idAplicativo;

        return JWT::encode(
            payload: (array) $objPayload,
            key: self::getKey(), // CORREÇÃO: Usando método para obter chave
            alg: self::ALGORITHM,
            keyId: null,
            head: (array) $objHeaders
        );
    }

    public function validateToken(string $stringToken): bool
    {
        if (empty($stringToken)) {
            return false;
        }

        // Remove "Bearer " se presente
        $token = str_replace(["Bearer ", " "], "", $stringToken);

        // Verifica padrão do token JWT
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        try {
            $payloadValido = JWT::decode(
                jwt: $token,
                keyOrKeyArray: new Key(self::getKey(), self::ALGORITHM) // CORREÇÃO: Usando método para obter chave
            );
            $this->setPayload($payloadValido);
            return true;
        } catch (SignatureInvalidException $e) {
            error_log("Token com assinatura inválida: " . $e->getMessage());
            return false;
        } catch (ExpiredException $e) {
            error_log("Token expirado: " . $e->getMessage());
            return false;
        } catch (BeforeValidException $e) {
            error_log("Token ainda não válido: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Erro ao validar token: " . $e->getMessage());
            return false;
        }
    }

    public function getPayload(): stdClass
    {
        return $this->payload;
    }

    public function setPayload(stdClass $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    // Método utilitário para extrair informações do usuário
    public function getUsuario(): stdClass
    {
        return $this->payload->public ?? new stdClass();
    }

    // Método utilitário para extrair informações privadas
    public function getDadosPrivados(): stdClass
    {
        return $this->payload->private ?? new stdClass();
    }
}
