<?php

namespace Jenssegers\Optimus;

use InvalidArgumentException;

class Optimus
{
    /**
     * @var int
     */
    const DEFAULT_MAX_INT = 2147483647;

    /**
     * @var string
     */
    private static $mode;

    /**
     * Use GMP extension functions.
     */
    const MODE_GMP = 'gmp';

    /**
     * Use native PHP implementation.
     */
    const MODE_NATIVE = 'native';

    /**
     * @var int
     */
    private $max;

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
     * @param int $max
     */
    public function __construct($prime, $inverse, $xor = 0, $max = null)
    {
        $this->prime = (int) $prime;
        $this->inverse = (int) $inverse;
        $this->xor = (int) $xor;
        $this->max = $max === null ? static::DEFAULT_MAX_INT : (int) $max;

        // Check which calculation mode should be used.
        if (static::$mode === null) {
            static::$mode = PHP_INT_SIZE === 4 ? static::MODE_GMP : static::MODE_NATIVE;
        }
    }

    /**
     * Encode an integer.
     *
     * @param  int $value
     *
     * @return int
     */
    public function encode($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        switch (static::$mode) {
            case self::MODE_GMP:
                return (gmp_intval(gmp_mul($value, $this->prime)) & $this->max) ^ $this->xor;

            default:
                return (((int) $value * $this->prime) & $this->max) ^ $this->xor;
        }
    }

    /**
     * Decode an integer.
     *
     * @param  int $value
     *
     * @return int
     */
    public function decode($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        switch (static::$mode) {
            case static::MODE_GMP:
                return gmp_intval(gmp_mul((int) $value ^ $this->xor, $this->inverse)) & $this->max;

            default:
                return (((int) $value ^ $this->xor) * $this->inverse) & $this->max;
        }
    }

    /**
     * Set the internal calculation mode (mainly used for testing).
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        if (! in_array($mode, [static::MODE_GMP, static::MODE_NATIVE])) {
            throw new InvalidArgumentException('Unkown mode: ' . $mode);
        }

        static::$mode = $mode;
    }

    /**
     * Set the maximum integer.
     *
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }
}
