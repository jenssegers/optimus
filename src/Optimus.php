<?php

namespace Jenssegers\Optimus;

use InvalidArgumentException;

class Optimus
{
    /**
     * Default bit size for of the max integer value.
     */
    const DEFAULT_SIZE = 31;

    /**
     * @var string
     */
    private $mode = 'native';

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

        // Switch to GMP if 32 bit system, or working with larger primes.
        if (PHP_INT_SIZE === 4 || $size > 31) {
            $this->setMode(static::MODE_GMP);
        }
    }

    public function encode(int $value): int
    {
        if ($this->mode === self::MODE_GMP) {
            return (gmp_intval(gmp_mul($value, $this->prime)) & $this->max) ^ $this->xor;
        }

        return (($value * $this->prime) & $this->max) ^ $this->xor;
    }

    public function decode(int $value): int
    {
        if ($this->mode === static::MODE_GMP) {
            return gmp_intval(gmp_mul($value ^ $this->xor, $this->inverse)) & $this->max;
        }

        return (($value ^ $this->xor) * $this->inverse) & $this->max;
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
