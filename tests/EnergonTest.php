<?php

use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Optimus;
use phpseclib\Math\BigInteger;

class EnergonTest extends PHPUnit_Framework_TestCase
{
    public function testGeneratesRandomSet()
    {
        $set = Energon::generate();

        $this->assertCount(3, $set);
        $this->assertInternalType('integer', $set[0]);
        $this->assertInternalType('integer', $set[1]);
        $this->assertInternalType('integer', $set[2]);
    }

    public function testGeneratesAskedSet()
    {
        $set = Energon::generate(1580030173);

        $this->assertCount(3, $set);
        $this->assertInternalType('integer', $set[0]);
        $this->assertInternalType('integer', $set[1]);
        $this->assertInternalType('integer', $set[2]);
        $this->assertEquals(1580030173, $set[0]);
        $this->assertEquals(59260789, $set[1]);
    }

    public function testRandomSetContainsExpectedNumbers()
    {
        $set = Energon::generate();

        $first = new BigInteger($set[0]);
        $x = new BigInteger(Optimus::MAX_INT + 1);

        $this->assertTrue($first->isPrime());
        $this->assertEquals($first->modInverse($x)->toString(), $set[1]);
    }

    public function testInvalidPrimeProvided()
    {
        $this->setExpectedException('Jenssegers\Optimus\Exceptions\InvalidPrimeException', '2');

        Energon::generate(2);
    }
}
