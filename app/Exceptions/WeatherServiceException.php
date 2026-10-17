<?php

namespace App\Exceptions;

use RuntimeException;

class WeatherServiceException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly int $status = 502,
    ) {
        parent::__construct($reason);
    }
}
