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
    private $bitLength;

    /**
     * @param int|null $prime
     * @param int $bitLength
     */
    public function __construct($prime = null, $bitLength = Optimus::DEFAULT_BIT_LENGTH)
    {
        if (is_null($prime)) {
            $prime = static::generatePrime($bitLength);
        }

        $this->setPrime($prime);
        $this->setBitLength($bitLength);
    }

    /**
     * Generates a set of numbers ready for use.
     *
     * @param int|null $prime
     *
     * @param int $bitLength
     * @return array
     */
    public static function generate($prime = null, $bitLength = Optimus::DEFAULT_BIT_LENGTH)
    {
        $instance = new static($prime, $bitLength);

        return [
            $instance->getPrime(),
            $instance->getInverse(),
            $instance->getRand(),
        ];
    }

    /**
     * Generate a random large prime.
     *
     * @param int $bitLength
     * @return int
     */
    public static function generatePrime($bitLength = Optimus::DEFAULT_BIT_LENGTH)
    {
        $max = self::createMaxInt($bitLength);
        $expForMin = max(1, floor(log10($max->toString())) - 2);
        $min = new BigInteger(pow(10, $expForMin));

        return (int) $max->randomPrime($min, $max)->toString();
    }

    /**
     * Calculate the modular multiplicative inverse of the prime number
     * @param int|BigInteger $prime
     * @param int $bitLength
     * @return int
     */
    public static function calculateInverse($prime, $bitLength = Optimus::DEFAULT_BIT_LENGTH)
    {
        if (!$prime instanceof BigInteger) {
            $prime = new BigInteger($prime);
        }

        $x = self::createMaxInt($bitLength)->add(new BigInteger(1));

        if (!$inverse = $prime->modInverse($x)) {
            throw new InvalidPrimeException($prime);
        }

        return (int) $inverse->toString();
    }

    /**
     * Generate a random large number.
     *
     * @param int $bitLength
     * @return int
     */
    public static function generateRandomInteger($bitLength = Optimus::DEFAULT_BIT_LENGTH)
    {
        return (int) (new BigInteger(hexdec(bin2hex(Random::string(4)))))
            ->bitwise_and(self::createMaxInt($bitLength))
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

    public function setBitLength($bits)
    {
        $this->bitLength = $bits;
    }

    /**
     * Get the inverse of the current prime.
     *
     * @return int
     */
    public function getInverse()
    {
        return self::calculateInverse($this->prime, $this->bitLength);
    }

    /**
     * Alias method for getting a random big number.
     *
     * @return int
     */
    public function getRand()
    {
        return static::generateRandomInteger($this->bitLength);
    }

    /**
     * @param int $bitLength
     * @return BigInteger
     */
    protected static function createMaxInt($bitLength)
    {
        return (new BigInteger(pow(2, $bitLength)))->subtract(new BigInteger(1));
    }
}
