<?php

namespace Jenssegers\Optimus;

use Jenssegers\Optimus\Exceptions\InvalidPrimeException;
use phpseclib\Crypt\Random;
use phpseclib\Math\BigInteger;

class Energon
{
    /**
     * @var BigInteger
     */
    protected $prime;

    /**
     * @var int
     */
    private $size;

    /**
     * @param int|null $prime
     * @param int $size
     */
    public function __construct($prime = null, $size = Optimus::DEFAULT_SIZE)
    {
        if (is_null($prime)) {
            $prime = static::generatePrime($size);
        }

        $this->setPrime($prime);
        $this->setSize($size);
    }

    /**
     * Generates a set of numbers ready for use.
     *
     * @param int|null $prime
     * @param int $size
     * @return array
     */
    public static function generate($prime = null, $size = Optimus::DEFAULT_SIZE)
    {
        $instance = new static($prime, $size);

        return [
            $instance->getPrime(),
            $instance->getInverse(),
            $instance->getRand(),
        ];
    }

    /**
     * Generate a random large prime.
     *
     * @param int $size
     * @return int
     */
    public static function generatePrime($size = Optimus::DEFAULT_SIZE)
    {
        $max = self::createMaxInt($size);
        $expForMin = max(1, floor(log10($max->toString())) - 2);
        $min = new BigInteger(pow(10, $expForMin));

        return (int) $max->randomPrime($min, $max)->toString();
    }

    /**
     * Calculate the modular multiplicative inverse of the prime number
     * @param int|BigInteger $prime
     * @param int $size
     * @return int
     */
    public static function calculateInverse($prime, $size = Optimus::DEFAULT_SIZE)
    {
        if (!$prime instanceof BigInteger) {
            $prime = new BigInteger($prime);
        }

        $max = self::createMaxInt($size)->add(new BigInteger(1));

        if (!$inverse = $prime->modInverse($max)) {
            throw new InvalidPrimeException($prime);
        }

        return (int) $inverse->toString();
    }

    /**
     * Generate a random large number.
     *
     * @param int $size
     * @return int
     */
    public static function generateRandomInteger($size = Optimus::DEFAULT_SIZE)
    {
        return (int) (new BigInteger(hexdec(bin2hex(Random::string(4)))))
            ->bitwise_and(self::createMaxInt($size))
            ->toString();
    }

    /**
     * Get the current prime.
     *
     * @return int
     */
    public function getPrime()
    {
        return (int) $this->prime->toString();
    }

    /**
     * Safely set the current prime as a BigInteger.
     *
     * @param int|BigInteger $prime
     */
    public function setPrime($prime)
    {
        if (!$prime instanceof BigInteger) {
            $prime = new BigInteger($prime);
        }

        if (!$prime->isPrime()) {
            throw new InvalidPrimeException($prime);
        }

        $this->prime = $prime;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Get the inverse of the current prime.
     *
     * @return int
     */
    public function getInverse()
    {
        return self::calculateInverse($this->prime, $this->size);
    }

    /**
     * Alias method for getting a random big number.
     *
     * @return int
     */
    public function getRand()
    {
        return static::generateRandomInteger($this->size);
    }

    /**
     * @param int $size
     * @return BigInteger
     */
    protected static function createMaxInt($size)
    {
        return (new BigInteger(pow(2, $size)))->subtract(new BigInteger(1));
    }
}
