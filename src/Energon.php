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
     * @var BigInteger
     */
    protected $max;

    /**
     * @param int|null $prime
     * @param int $max
     */
    public function __construct($prime = null, $max = Optimus::MAX_INT)
    {
        if (is_null($prime)) {
            $prime = static::generatePrime($max);
        }

        $this->setPrime($prime);
        $this->setMax($max);
    }

    /**
     * Generates a set of numbers ready for use.
     *
     * @param int|null $prime
     * @param int $max
     * @return array
     */
    public static function generate($prime = null, $max = Optimus::MAX_INT)
    {
        $instance = new static($prime, $max);

        return [
            $instance->getPrime(),
            $instance->getInverse(),
            $instance->getRand(),
        ];
    }

    /**
     * Generate a random large prime.
     *
     * @param int $max
     * @return int
     */
    public static function generatePrime($max = Optimus::MAX_INT)
    {
        if (!$max instanceof BigInteger) {
            $max = new BigInteger($max);
        }

        $expForMin = max(1, floor(log10($max->toString())) - 2);
        $min = new BigInteger(pow(10, $expForMin));

        return (int) $max->randomPrime($min, $max)->toString();
    }

    /**
     * Calculate the modular multiplicative inverse of the prime number
     * @param int|BigInteger $prime
     * @param int $max
     * @return int
     */
    public static function calculateInverse($prime, $max = Optimus::MAX_INT)
    {
        if (!$prime instanceof BigInteger) {
            $prime = new BigInteger($prime);
        }

        if (!$max instanceof BigInteger) {
            $max = new BigInteger($max);
        }

        if (!$inverse = $prime->modInverse($max->add(new BigInteger(1)))) {
            throw new InvalidPrimeException($prime);
        }

        return (int) $inverse->toString();
    }

    /**
     * Generate a random large number.
     *
     * @param int $max
     * @return int
     */
    public static function generateRandomInteger($max = Optimus::MAX_INT)
    {
        if (!$max instanceof BigInteger) {
            $max = new BigInteger($max);
        }

        return (int) (new BigInteger(hexdec(bin2hex(Random::string(4)))))
            ->bitwise_and($max)
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

    public function setMax($max)
    {
        if (!$max instanceof BigInteger) {
            $max = new BigInteger($max);
        }

        $this->max = $max;
    }

    /**
     * Get the inverse of the current prime.
     *
     * @return int
     */
    public function getInverse()
    {
        return self::calculateInverse($this->prime, $this->max);
    }

    /**
     * Alias method for getting a random big number.
     *
     * @return int
     */
    public function getRand()
    {
        return static::generateRandomInteger($this->max);
    }
}
