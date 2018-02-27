<?php

namespace Jenssegers\Optimus;

use InvalidArgumentException;

class Optimus
{
    /**
     * @var int
     * @deprecated The maximum integer is now configurable via the bit length.
     */
    const MAX_INT = 2147483647;

    const DEFAULT_MAX_BITS = 31;

    /**
     * @var string
     */
    private $mode;

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
    private $prime;

    /**
     * @var int
     */
    private $inverse;

    /**
     * @var int
     */
    private $xor;

    private $maxInt;

    /**
     * @param int $prime
     * @param int $xor
     * @param int $inverse
     */
    public function __construct($prime, $inverse, $xor = 0, $maxBits = self::DEFAULT_MAX_BITS)
    {
        $this->prime = (int) $prime;
        $this->inverse = (int) $inverse;
        $this->xor = (int) $xor;
        $this->maxInt = (int) pow(2, $maxBits)-1;

        // Check which calculation mode should be used.
        $this->mode = PHP_INT_SIZE === 4 || $maxBits > 31 ? static::MODE_GMP : static::MODE_NATIVE;

        if ($this->mode == static::MODE_GMP && !extension_loaded(static::MODE_GMP)) {
            throw new \RuntimeException(
                "The GNU Multiple Precision functions are required for calculations on your system."
            );
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

        switch ($this->mode) {
            case self::MODE_GMP:
                return (gmp_intval(gmp_mul($value, $this->prime)) & $this->maxInt) ^ $this->xor;

            default:
                return (((int) $value * $this->prime) & $this->maxInt) ^ $this->xor;
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

        switch ($this->mode) {
            case static::MODE_GMP:
                return gmp_intval(gmp_mul((int) $value ^ $this->xor, $this->inverse)) & $this->maxInt;

            default:
                return (((int) $value ^ $this->xor) * $this->inverse) & $this->maxInt;
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

        $this->mode = $mode;
    }
}
