<?php

declare(strict_types=1);

class Response
{
    private bool $success;
    private string $message;
    private array $data;
    private array $error;
    private int $httpCode;

    public function __construct(
        bool $success,
        string $message,
        array $data = [],
        array $error = [],
        int $httpCode = 200
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->error = $error;
        $this->httpCode = $httpCode;
    }

    public function send(): void
    {
        http_response_code($this->httpCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'error' => $this->error
        ]);
    }
}
