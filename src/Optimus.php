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

    /**
     * Default bit size for of the max integer value.
     */
    const DEFAULT_SIZE = 31;

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

    public function __construct(int $prime, int $inverse, int $xor = 0, int $size = self::DEFAULT_SIZE)
    {
        $this->prime = $prime;
        $this->inverse = $inverse;
        $this->xor = $xor;
        $this->max = 2 ** $size - 1;

        $this->detectCalculationMode();
    }

    private function detectCalculationMode()
    {
        // 32 bit systems should use GMP.
        $this->setMode(PHP_INT_SIZE === 4 ? static::MODE_GMP : static::MODE_NATIVE);
    }

    public function encode(int $value): int
    {
        $doMode = $this->mode;

        if ($doMode === self::MODE_NATIVE
            && PHP_INT_SIZE === 8
            && ($value * $this->prime) > 9e18
        ) {
            $doMode = self::MODE_GMP;
        }

        if ($doMode === self::MODE_GMP) {
            return (gmp_intval(gmp_mul($value, $this->prime)) & $this->max) ^ $this->xor;
        }

        return (($value * $this->prime) & $this->max) ^ $this->xor;
    }

    public function decode(int $value): int
    {
        $valXored = $value ^ $this->xor;

        $doMode = $this->mode;

        if ($doMode === self::MODE_NATIVE
            && PHP_INT_SIZE === 8
            && ($valXored * $this->inverse) > 9e18
        ) {
            $doMode = self::MODE_GMP;
        }

        if ($doMode === static::MODE_GMP) {
            return gmp_intval(gmp_mul($valXored, $this->inverse)) & $this->max;
        }

        return ($valXored * $this->inverse) & $this->max;
    }

    public function setMode(string $mode)
    {
        if (!in_array($mode, [static::MODE_GMP, static::MODE_NATIVE])) {
            throw new InvalidArgumentException('Unknown mode: ' . $mode);
        }

        if ($mode === static::MODE_GMP && !extension_loaded('gmp')) {
            throw new InvalidArgumentException(
                'The GNU Multiple Precision functions are required for calculations on your system.'
            );
        }

        $this->mode = $mode;
    }
}
