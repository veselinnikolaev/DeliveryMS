<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    /**
     * DatabaseException constructor
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception for chaining
     */
    public function __construct(string $message = "Database operation failed", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
