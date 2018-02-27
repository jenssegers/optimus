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

    const DEFAULT_BIT_LENGTH = 31;

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

    /**
     * @var int
     */
    private $maxInt;

    /**
     * @param int $prime
     * @param int $inverse
     * @param int $xor
     * @param int $bitLength
     */
    public function __construct($prime, $inverse, $xor = 0, $bitLength = self::DEFAULT_BIT_LENGTH)
    {
        $this->prime = (int) $prime;
        $this->inverse = (int) $inverse;
        $this->xor = (int) $xor;
        $this->maxInt = (int) pow(2, $bitLength)-1;

        // Check which calculation mode should be used.
        $this->mode = PHP_INT_SIZE === 4 ? static::MODE_GMP : static::MODE_NATIVE;

        if (($this->mode == static::MODE_GMP || $bitLength > 31) && !extension_loaded(static::MODE_GMP)) {
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

        $doMode = $this->mode;

        if ($doMode === self::MODE_NATIVE
            && PHP_INT_SIZE == 8
            && ($value * $this->prime) > 9e18
        ) {
            $doMode = self::MODE_GMP;
        }

        switch ($doMode) {
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

        $valXored = (int) $value ^ $this->xor;

        $doMode = $this->mode;

        if ($doMode === self::MODE_NATIVE
            && PHP_INT_SIZE == 8
            && ($valXored * $this->inverse) > 9e18
        ) {
            $doMode = self::MODE_GMP;
        }

        switch ($doMode) {
            case static::MODE_GMP:
                return gmp_intval(gmp_mul($valXored, $this->inverse)) & $this->maxInt;

            default:
                return ($valXored * $this->inverse) & $this->maxInt;
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
