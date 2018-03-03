<?php

use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Optimus;
use phpseclib\Math\BigInteger;

class EnergonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getCalculateInverseTestData
     * @param $bitLength
     * @param $prime
     * @param $expectedInverse
     */
    public function testCalculateInverseWithDifferentPrimeTypes($bitLength, $prime, $expectedInverse)
    {
        $this->assertSame($expectedInverse, Energon::calculateInverse($prime, $bitLength));
    }

    public function getCalculateInverseTestData()
    {
        return [
            [31, 1580030173, 59260789],
            [31, new BigInteger(1580030173), 59260789],
        ];
    }

    /**
     * @dataProvider getBitLengths
     * @param int $bitLength
     */
    public function testGeneratesRandomSet($bitLength)
    {
        $set = Energon::generate(null, $bitLength);

        $this->assertCount(3, $set);
        $this->assertInternalType('integer', $set[0], 'Unexpected type for prime number.');
        $this->assertInternalType('integer', $set[1], 'Unexpected type for inverse number.');
        $this->assertInternalType('integer', $set[2], 'Unexpected type for Xor.');
        $this->assertSame(
            '1',
            (new BigInteger($set[0]))
                ->multiply(new BigInteger($set[1]))
                ->bitwise_and(new BigInteger(pow(2, $bitLength) - 1))
                ->toString(),
            sprintf(
                'Prime: %s, Inverse: %s, Xor: %s, Bit length: %s',
                $set[0],
                $set[1],
                $set[2],
                $bitLength
            )
        );
    }

    public function getBitLengths()
    {
        return [
            [31],
            [32],
            [24],
            [16],
        ];
    }

    /**
     * @dataProvider getAskedSetTestData
     */
    public function testGeneratesAskedSet($bitLength, $prime, $expectedInverse)
    {
        $set = Energon::generate($prime, $bitLength);

        $this->assertCount(3, $set);
        $this->assertInternalType('integer', $set[0], 'Unexpected type for prime number.');
        $this->assertInternalType('integer', $set[1], 'Unexpected type for inverse number.');
        $this->assertInternalType('integer', $set[2], 'Unexpected type for Xor.');
        $this->assertEquals($prime, $set[0], 'Unexpected prime number.');
        $this->assertEquals($expectedInverse, $set[1], 'Unexpected inverse number.');
    }

    public function getAskedSetTestData()
    {
        return [
            [31, 1580030173, 59260789],
            [32, 1580030173, 59260789],
            [24, 12105601, 15698049],
            [16, 1588507, 54547]
        ];
    }

    public function testRandomSetContainsExpectedNumbers()
    {
        $set = Energon::generate();

        $first = new BigInteger($set[0]);
        $x = new BigInteger(pow(2, Optimus::DEFAULT_BIT_LENGTH));

        $this->assertTrue($first->isPrime());
        $this->assertEquals($first->modInverse($x)->toString(), $set[1]);
    }

    public function testInvalidPrimeProvided()
    {
        $this->setExpectedException('Jenssegers\Optimus\Exceptions\InvalidPrimeException', '2');

        Energon::generate(2);
    }
}
