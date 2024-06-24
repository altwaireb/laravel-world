<?php

namespace Altwaireb\World\Exceptions;

use Exception;

class InvalidCodeException extends Exception
{
    public static function iso2CodeNotFound(string $code): InvalidCodeException
    {
        return new self(message: "Not Found Country has iso2 with this value $code.");
    }

    public static function iso3CodeNotFound(string $code): InvalidCodeException
    {
        return new self(message: "Not Found Country has iso3 with this value $code.");
    }
}
