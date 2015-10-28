<?php namespace Jenssegers\Optimus;

use InvalidArgumentException;

class Optimus {

    /**
     * @var int
     */
    const MAX_INT = 2147483647;

    /**
     * @var string
     */
    private static $mode;

    /**
     * @var int
     */
    private $prime;

    /**
     * @var int
     */
    private $inverse;

    /**
     * @var int
     */
    private $xor;

    /**
     * @param int $prime
     * @param int $xor
     * @param int $inverse
     */
    public function __construct($prime, $inverse, $xor = 0)
    {
        $this->prime = (int) $prime;
        $this->inverse = (int) $inverse;
        $this->xor = (int) $xor;

        // Check which calculation mode we need to use.
        if (static::$mode === null)
        {
            static::$mode = PHP_INT_SIZE === 4 ? 'gmp' : 'native';
        }
    }

    /**
     * Encode an integer.
     *
     * @param  int $value
     * @return int
     */
    public function encode($value)
    {
        if ( ! is_numeric($value))
        {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        switch (static::$mode)
        {
            case 'gmp':
                return ((int) gmp_mul($value, $this->prime) & static::MAX_INT) ^ $this->xor;

            default:
                return (((int) $value * $this->prime) & static::MAX_INT) ^ $this->xor;
        }
    }

    /**
     * Decode an integer.
     *
     * @param  int $value
     * @return int
     */
    public function decode($value)
    {
        if ( ! is_numeric($value))
        {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        switch (static::$mode)
        {
            case 'gmp':
                return (int) gmp_mul((int) $value ^ $this->xor, $this->inverse) & static::MAX_INT;

            default:
                return (((int) $value ^ $this->xor) * $this->inverse) & static::MAX_INT;
        }
    }

}
