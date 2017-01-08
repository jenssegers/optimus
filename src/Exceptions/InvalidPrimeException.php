<?php

namespace Jenssegers\Optimus\Exceptions;

use Exception;
use phpseclib\Math\BigInteger;
use RangeException;

class InvalidPrimeException extends RangeException
{
    /**
     * InvalidPrimeException constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if ($message instanceof BigInteger) {
            $message = $message->toString();
        }

        parent::__construct($message, $code, $previous);
    }
}
