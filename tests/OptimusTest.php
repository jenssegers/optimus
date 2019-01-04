<?php

namespace Jenssegers\Optimus\Tests;

use InvalidArgumentException;
use Jenssegers\Optimus\Energon;
use Jenssegers\Optimus\Optimus;
use PHPUnit\Framework\TestCase;

class OptimusTest extends TestCase
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

    public function testEncodeStrings()
    {
        $this->assertEquals($this->optimus->encode(20), $this->optimus->encode('20'));
        $this->assertEquals($this->optimus->decode(1440713122), $this->optimus->decode('1440713122'));
    }

    public function testGmpMode()
    {
        $this->optimus->setMode(Optimus::MODE_GMP);

        for ($i = 0; $i < 1000; $i++) {
            $id = random_int(0, Optimus::MAX_INT);
            $encoded = $this->optimus->encode($id);
            $decoded = $this->optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertNotEquals($id, $encoded);
        }
    }

    public function testSetModeShouldThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->optimus->setMode('invalid_mode');
    }
}
