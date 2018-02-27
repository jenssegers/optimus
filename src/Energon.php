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
    private $maxBits;

    /**
     * @param int|null $prime
     */
    public function __construct($prime = null, $maxBits = Optimus::DEFAULT_MAX_BITS)
    {
        if (is_null($prime)) {
            $prime = static::generatePrime($maxBits);
        }

        $this->setPrime($prime);
        $this->setMaxBits($maxBits);
    }

    /**
     * Generates a set of numbers ready for use.
     *
     * @param int|null $prime
     *
     * @return array
     */
    public static function generate($prime = null, $maxBits = Optimus::DEFAULT_MAX_BITS)
    {
        $instance = new static($prime, $maxBits);

        return [
            $instance->getPrime(),
            $instance->getInverse(),
            $instance->getRand(),
        ];
    }

    /**
     * Generate a random large prime.
     *
     * @return int
     */
    public static function generatePrime($maxBits = Optimus::DEFAULT_MAX_BITS)
    {
        $max = self::createMaxInt($maxBits);
        $expForMin =  max(1,floor(log10($max->toString()))-2);
        $min = new BigInteger(pow(10, $expForMin));

        return (int) $max->randomPrime($min, $max)->toString();
    }

    /**
     * Calculate the modular multiplicative inverse of the prime number
     * @param int|BigInteger $prime
     * @return int
     */
    public static function calculateInverse($prime, $maxBits = Optimus::DEFAULT_MAX_BITS)
    {
        if (!$prime instanceof BigInteger) {
            $prime = new BigInteger($prime);
        }

        $x = self::createMaxInt($maxBits)->add(new BigInteger(1));

        if (! $inverse = $prime->modInverse($x)) {
            throw new InvalidPrimeException($prime);
        }

        return (int) $inverse->toString();
    }

    /**
     * Generate a random large number.
     *
     * @return int
     */
    public static function generateRandomInteger($maxBits = Optimus::DEFAULT_MAX_BITS)
    {
        return (int) (new BigInteger(hexdec(bin2hex(Random::string(4)))))
            ->bitwise_and(self::createMaxInt($maxBits))
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
        if (! $prime instanceof BigInteger) {
            $prime = new BigInteger($prime);
        }

        if (! $prime->isPrime()) {
            throw new InvalidPrimeException($prime);
        }

        $this->prime = $prime;
    }

    public function setMaxBits($bits)
    {
        $this->maxBits = $bits;
    }

    /**
     * Get the inverse of the current prime.
     *
     * @return int
     */
    public function getInverse()
    {
        return self::calculateInverse($this->prime, $this->maxBits);
    }

    /**
     * Alias method for getting a random big number.
     *
     * @return int
     */
    public function getRand()
    {
        return static::generateRandomInteger($this->maxBits);
    }

    /**
     * @param int $maxBits
     * @return BigInteger
     */
    protected static function createMaxInt($maxBits)
    {
        return (new BigInteger(pow(2, $maxBits)))->subtract(new BigInteger(1));
    }
}
