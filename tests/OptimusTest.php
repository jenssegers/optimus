<?php

namespace Jenssegers\Optimus\Tests;

use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Optimus;
use PHPUnit\Framework\TestCase;

class OptimusTest extends TestCase
{
    /**
     * @dataProvider getBitLengthTestData
     */
    public function testEncodeDecodeRandomNumbers(int $bitLength)
    {
        $maxInt = (2 ** $bitLength) - 1;
        list($prime, $inverse, $xor) = Energon::generate(null, $bitLength);

        $optimus = new Optimus($prime, $inverse, $xor, $bitLength);

        for ($i = 0; $i < 1000; $i++) {
            $id = random_int(0, $maxInt);

            $encoded = $optimus->encode($id);
            $decoded = $optimus->decode($encoded);

            $assertMsgDetails = sprintf(
                'Prime: %s, Inverse: %s, Xor: %s, Bit length: %s, Value: %s',
                $prime,
                $inverse,
                $xor,
                $bitLength,
                $id
            );

            $this->assertEquals(
                $id,
                $decoded,
                "Encoded value $encoded has not decoded back to $id. ($assertMsgDetails)"
            );

            $this->assertNotEquals(
                $id,
                $encoded,
                "Encoded value $encoded matches the original value."
            );
        }
    }

    public function getBitLengthTestData(): array
    {
        return [
            [31],
            [62],
            [48],
            [32],
            [24],
            [16],
        ];
    }

    /**
     * @dataProvider getBitLengthTestData
     */
    public function testEncodeDecodeRandomNumbersWithGmp(int $bitLength)
    {
        $maxInt = (2 ** $bitLength) - 1;
        list($prime, $inverse, $xor) = Energon::generate(null, $bitLength);

        $optimus = new Optimus($prime, $inverse, $xor, $bitLength);
        $optimus->setMode(Optimus::MODE_GMP);

        for ($i = 0; $i < 1000; $i++) {
            $id = random_int(0, $maxInt);

            $encoded = $optimus->encode($id);
            $decoded = $optimus->decode($encoded);

            $assertMsgDetails = sprintf(
                'Prime: %s, Inverse: %s, Xor: %s, Bit length: %s, Value: %s',
                $prime,
                $inverse,
                $xor,
                $bitLength,
                $id
            );

            $this->assertEquals(
                $id,
                $decoded,
                "Encoded value $encoded has not decoded back to $id. ($assertMsgDetails)"
            );

            $this->assertNotEquals(
                $id,
                $encoded,
                "Encoded value $encoded matches the original value."
            );
        }
    }

    public function testEncodeStrings()
    {
        list($prime, $inverse, $xor) = Energon::generate();
        $optimus = new Optimus($prime, $inverse, $xor);

        $this->assertEquals($optimus->encode(20), $optimus->encode('20'));
        $this->assertEquals($optimus->decode(1440713122), $optimus->decode('1440713122'));
    }
}
