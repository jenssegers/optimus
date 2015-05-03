<?php namespace Jenssegers\Optimus;

class Optimus {

    /**
     * @var integer
     */
    const MAX_INT = 2147483647;

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
     * @param integer  $prime
     * @param integer  $xor
     * @param integer  $inverse
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
     * @param  integer  $value
     * @return integer
     */
    public function encode($value)
    {
        return (((int) $value * $this->prime) & static::MAX_INT) ^ $this->xor;
    }

    /**
     * Decode an integer.
     *
     * @param  integer  $value
     * @return integer
     */
    public function decode($value)
    {
        return (((int) $value ^ $this->xor) * $this->inverse) & static::MAX_INT;
    }

}
