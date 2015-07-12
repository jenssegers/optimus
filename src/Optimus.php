<?php namespace Jenssegers\Optimus;

use InvalidArgumentException;

class Optimus {

    /**
     * @var int
     */
    const MAX_INT = 2147483647;

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

        return (((int) $value * $this->prime) & static::MAX_INT) ^ $this->xor;
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

        return (((int) $value ^ $this->xor) * $this->inverse) & static::MAX_INT;
    }

}
