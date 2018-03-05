<?php

namespace Jenssegers\Optimus;

use InvalidArgumentException;
use RuntimeException;

class Optimus
{
    /**
     * @var int
     */
    const MAX_INT = 2147483647;

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
    private $max;

    /**
     * @param int $prime
     * @param int $inverse
     * @param int $xor
     * @param int $max
     */
    public function __construct($prime, $inverse, $xor = 0, $max = 2147483647)
    {
        $this->prime = (int) $prime;
        $this->inverse = (int) $inverse;
        $this->xor = (int) $xor;
        $this->max =  (int) $max;

        // 32 bit systems should definitely use GMP.
        $this->mode = PHP_INT_SIZE === 4 ? static::MODE_GMP : static::MODE_NATIVE;

        // For larger numbers than 2147483647, GMP should be used as well.
        if ($this->max > 2147483647) {
            $this->mode = static::MODE_GMP;
        }

        // GMP is better at handling big numbers.
        if ($this->mode === static::MODE_GMP && !extension_loaded(static::MODE_GMP)) {
            throw new RuntimeException(
                'The GNU Multiple Precision functions are required for calculations on your system.'
            );
        }

        // Always use GMP if available.
        if (extension_loaded(static::MODE_GMP)) {
            $this->mode = static::MODE_GMP;
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
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        switch ($this->mode) {
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
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Argument should be an integer');
        }

        $valXored = (int) $value ^ $this->xor;

        $doMode = $this->mode;

//        if ($doMode === self::MODE_NATIVE
//            && PHP_INT_SIZE == 8
//            && ($valXored * $this->inverse) > 9e18
//        ) {
//            $doMode = self::MODE_GMP;
//        }

        switch ($doMode) {
            case static::MODE_GMP:
                return gmp_intval(gmp_mul($value ^ $this->xor, $this->inverse)) & $this->max;

            default:
                return (($value ^ $this->xor) * $this->inverse) & $this->max;
        }
    }

    /**
     * Set the internal calculation mode (mainly used for testing).
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        if (!in_array($mode, [static::MODE_GMP, static::MODE_NATIVE])) {
            throw new InvalidArgumentException('Unknown mode: ' . $mode);
        }

        $this->mode = $mode;
    }
}
