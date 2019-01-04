<?php

namespace Jenssegers\Optimus;

use phpseclib\Math\BigInteger;

class Optimus
{
    /**
     * Default bit size for of the max integer value.
     */
    const DEFAULT_SIZE = 31;

    /**
     * @var BigInteger
     */
    private $prime;

    /**
     * @var BigInteger
     */
    private $inverse;

    /**
     * @var BigInteger
     */
    private $xor;

    /**
     * @var BigInteger
     */
    private $max;

    public function __construct(int $prime, int $inverse, int $xor = 0, int $size = self::DEFAULT_SIZE)
    {
        $this->prime = new BigInteger($prime);
        $this->inverse = new BigInteger($inverse);
        $this->xor = new BigInteger($xor);
        $this->max = new BigInteger(2 ** $size - 1);
    }

    public function encode(int $value): int
    {
        return (int) (new BigInteger($value))
            ->multiply($this->prime)
            ->bitwise_and($this->max)
            ->bitwise_xor($this->xor)
            ->toString();
    }

    public function decode(int $value): int
    {
        return (int) (new BigInteger($value))
            ->bitwise_xor($this->xor)
            ->multiply($this->inverse)
            ->bitwise_and($this->max)
            ->toString();
    }
}
