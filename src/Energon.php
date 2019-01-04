<?php

namespace Jenssegers\Optimus;

use Jenssegers\Optimus\Exceptions\InvalidPrimeException;
use phpseclib\Crypt\Random;
use phpseclib\Math\BigInteger;

class Energon
{
    /**
     * @var int
     */
    protected $prime;

    /**
     * @var int
     */
    private $size;

    public function __construct(int $prime = null, int $size = Optimus::DEFAULT_SIZE)
    {
        $this->setPrime($prime ?? static::generatePrime($size));
        $this->setSize($size);
    }

    public static function generate(int $prime = null, int $size = Optimus::DEFAULT_SIZE): array
    {
        $instance = new static($prime, $size);

        return [
            $instance->getPrime(),
            $instance->getInverse(),
            $instance->getRand(),
        ];
    }

    public static function generatePrime(int $size = Optimus::DEFAULT_SIZE): int
    {
        $max = self::createMaxInt($size);
        $expForMin = max(1, floor(log10($max->toString())) - 2);
        $min = new BigInteger(10 ** $expForMin);

        return (int) $max->randomPrime($min, $max)->toString();
    }

    public static function calculateInverse(int $prime, int $size = Optimus::DEFAULT_SIZE): int
    {
        $max = self::createMaxInt($size)->add(new BigInteger(1));
        $inverse = (new BigInteger($prime))->modInverse($max);

        if (!$inverse) {
            throw new InvalidPrimeException($prime);
        }

        return (int) $inverse->toString();
    }

    public static function generateRandomInteger(int $size = Optimus::DEFAULT_SIZE): int
    {
        return (int) (new BigInteger(hexdec(bin2hex(Random::string(4)))))
            ->bitwise_and(self::createMaxInt($size))
            ->toString();
    }

    public function getPrime(): int
    {
        return $this->prime;
    }

    public function setPrime(int $prime)
    {
        if (!(new BigInteger($prime))->isPrime()) {
            throw new InvalidPrimeException($prime);
        }

        $this->prime = $prime;
    }

    public function setSize(int $size)
    {
        $this->size = $size;
    }

    public function getInverse(): int
    {
        return self::calculateInverse($this->prime, $this->size);
    }

    public function getRand(): int
    {
        return static::generateRandomInteger($this->size);
    }

    protected static function createMaxInt(int $size): BigInteger
    {
        return (new BigInteger(2 ** $size))->subtract(new BigInteger(1));
    }
}
