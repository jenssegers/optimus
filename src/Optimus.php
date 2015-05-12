<?php namespace Jenssegers\Optimus;

use InvalidArgumentException;

class Optimus {

    /**
     * @var integer
     */
    private $prime;

    /**
     * @var integer
     */
    private $inverse;

    /**
     * @var integer
     */
    private $xor;

    /**
     * @param integer $prime
     * @param integer $xor
     * @param integer $inverse
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
     * @param  integer $value
     * @return integer
     */
    public function encode($value)
    {
        if ( ! is_numeric($value))
        {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        return (((int) $value * $this->prime) & PHP_INT_MAX) ^ $this->xor;
    }

    /**
     * Decode an integer.
     *
     * @param  integer $value
     * @return integer
     */
    public function decode($value)
    {
        if ( ! is_numeric($value))
        {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        return (((int) $value ^ $this->xor) * $this->inverse) & PHP_INT_MAX;
    }

}
