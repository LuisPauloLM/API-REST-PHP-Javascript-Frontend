<?php
require_once __DIR__ . '/../http/Response.php';
require_once __DIR__ . '/../utils/MeuTokenJWT.php';

class JWTMiddleware
{
    function getAuthorizationHeader()
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);
            if (isset($requestHeaders['authorization'])) {
                $headers = trim($requestHeaders['authorization']);
            }
        }

        return $headers;
    }

    public function isValidToken(): stdClass
    {
        $token = $this->getAuthorizationHeader();

        if (!isset($token)) {
            (new Response(
                success: false,
                message: 'Token não fornecido',
                error: [
                    'code' => 'auth_error',
                    'message' => 'Token de autenticação não foi fornecido',
                ],
                httpCode: 401
            ))->send();
            exit();
        }

        $Jwt = new MeuTokenJWT();

        if ($Jwt->validateToken($token)) {
            return $Jwt->getPayload();
        } else {
            (new Response(
                success: false,
                message: 'Token inválido',
                error: [
                    'code' => 'auth_error',
                    'message' => 'Token de autenticação inválido ou expirado',
                ],
                httpCode: 401
            ))->send();
            exit();
        }
    }
}
