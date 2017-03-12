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
     * @param int|null $prime
     */
    public function __construct($prime = null)
    {
        if (is_null($prime)) {
            $prime = static::generatePrime();
        }

        $this->setPrime($prime);
    }

    /**
     * Generates a set of numbers ready for use.
     *
     * @param int|null $prime
     *
     * @return array
     */
    public static function generate($prime = null)
    {
        $instance = new static($prime);

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
    public static function generatePrime()
    {
        $min = new BigInteger(1e7);
        $max = new BigInteger(Optimus::MAX_INT);

        return (int) $max->randomPrime($min, $max)->toString();
    }

    /**
     * Generate a random large number.
     *
     * @return int
     */
    public static function generateRandomInteger()
    {
        return (int) hexdec(bin2hex(Random::string(4))) & Optimus::MAX_INT;
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
     * @param mixed $prime
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

    /**
     * Get the inverse of the current prime.
     *
     * @return int
     */
    public function getInverse()
    {
        $x = new BigInteger(Optimus::MAX_INT + 1);

        if (! $inverse = $this->prime->modInverse($x)) {
            throw new InvalidPrimeException($this->prime);
        }

        return (int) $inverse->toString();
    }

    /**
     * Alias method for getting a random big number.
     *
     * @return int
     */
    public function getRand()
    {
        return static::generateRandomInteger();
    }
}
