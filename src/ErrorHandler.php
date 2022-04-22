<?php

class ErrorHandler
{
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): void {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);

        $log =  json_encode([
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);

        echo $log;

        // echo json_encode(['state' => 'bad request']);
    }
}
