<?php

namespace Altwaireb\World\Exceptions;

use Exception;

class IsoCodesIsEmptyException extends Exception
{
    public static function isEmpty(): IsoCodesIsEmptyException
    {
        return new self(message: "Iso Code Is Empty , please add ISO codes in config file 'config/world.php");
    }
}
