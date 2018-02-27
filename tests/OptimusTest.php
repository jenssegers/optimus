<?php

use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Optimus;

class OptimusTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Optimus
     */
    private $optimus;

    public function setUp()
    {
        list($prime, $inverse, $xor) = Energon::generate();
        $this->optimus = new Optimus($prime, $inverse, $xor);
    }

    /**
     * @dataProvider getPrimesTestData
     * @param $prime
     * @param $inverse
     * @param $xor
     * @param $maxBits
     * @param $value
     */
    public function testEncodeDecode($prime, $inverse, $xor, $maxBits, $value)
    {
        $optimus = new Optimus($prime, $inverse, $xor, $maxBits);

        $encoded = $optimus->encode($value);
        $decoded = $optimus->decode($encoded);

        $assertMsgDetails = sprintf(
            'Prime: %s, Inverse: %s, Xor: %s, MaxBits: %s, Value: %s',
            $prime,
            $inverse,
            $xor,
            $maxBits,
            $value
        );

        $this->assertNotEquals(
            $value,
            $encoded,
            "The encoded value is not different to the original value. ($assertMsgDetails)"
        );
        $this->assertNotEquals(
            $encoded,
            $decoded,
            "The encoded and decoded values are equal. ($assertMsgDetails)"
        );
        $this->assertEquals(
            $value,
            $decoded,
            "The encoded value did not decode correctly. ($assertMsgDetails)"
        );
    }

    public function getPrimesTestData()
    {
        $maxBit31 = 31;
        $maxBit32 = 32;
        $maxBit24 = 24;

        $maxBits = [
            $maxBit31,
            $maxBit32,
            $maxBit24
        ];

        $randXor = 873691988;
        $smlPrime = 10000019;

        $lrgPrimes = [
            $maxBit31 => 2147483647,
            $maxBit32 => 4294967291,
            $maxBit24 => 999999967,
        ];

        $smlPrimeInverses = [
            $maxBit31 => Energon::calculateInverse($smlPrime, $maxBit31),
            $maxBit32 => Energon::calculateInverse($smlPrime, $maxBit32),
            $maxBit24 => Energon::calculateInverse($smlPrime, $maxBit24)
        ];

        $lrgPrimeInverses = [
            $maxBit31 => Energon::calculateInverse($lrgPrimes[$maxBit31], $maxBit31),
            $maxBit32 => Energon::calculateInverse($lrgPrimes[$maxBit32], $maxBit32),
            $maxBit24 => Energon::calculateInverse($lrgPrimes[$maxBit24], $maxBit24)
        ];

        $testData = [];

        foreach ($maxBits as $maxBit) {
            $testData = array_merge(
                $testData,
                [
                    [$smlPrime, $smlPrimeInverses[$maxBit], 0, $maxBit, 1],
                    [$smlPrime, $smlPrimeInverses[$maxBit], 0, $maxBit, $maxBit],
                    [$smlPrime, $smlPrimeInverses[$maxBit], $randXor, $maxBit, 1],
                    [$smlPrime, $smlPrimeInverses[$maxBit], $randXor, $maxBit, $maxBit],
                    [$lrgPrimes[$maxBit], $lrgPrimeInverses[$maxBit], 0, $maxBit, 1],
                    [$lrgPrimes[$maxBit], $lrgPrimeInverses[$maxBit], 0, $maxBit, $maxBit],
                    [$lrgPrimes[$maxBit], $lrgPrimeInverses[$maxBit], $randXor, $maxBit, 1],
                    [$lrgPrimes[$maxBit], $lrgPrimeInverses[$maxBit], $randXor, $maxBit, $maxBit],
                ]
            );
        }

        return $testData;
    }

    /**
     * @dataProvider getMaxIntTestData
     * @param $maxBits
     */
    public function testEncodeDecodeRandomNumbers($maxBits)
    {
        $maxInt = pow(2, $maxBits);
        list($prime, $inverse, $xor) = Energon::generate(null, $maxBits);

        $optimus = new Optimus($prime, $inverse, $xor, $maxBits);

        for ($i = 0; $i < 1000; $i++) {
            $id = rand(0, $maxInt);
            $encoded = $optimus->encode($id);
            $decoded = $optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertNotEquals($id, $encoded);
        }
    }

    public function getMaxIntTestData()
    {
        return [
            [31],
            [32],
            [24],
            [16]
        ];
    }

    public function testEncodeStrings()
    {
        $this->assertEquals($this->optimus->encode(20), $this->optimus->encode('20'));
        $this->assertEquals($this->optimus->decode(1440713122), $this->optimus->decode('1440713122'));
    }

    public function testEncodeBadStrings()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->optimus->encode('foo');
    }

    public function testDecodeBadStrings()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->optimus->decode('foo');
    }

    public function testGmpMode()
    {
        $this->optimus->setMode(Optimus::MODE_GMP);

        for ($i = 0; $i < 1000; $i++) {
            $id = rand(0, Optimus::MAX_INT);
            $encoded = $this->optimus->encode($id);
            $decoded = $this->optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertNotEquals($id, $encoded);
        }
    }
}
