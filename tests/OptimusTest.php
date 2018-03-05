<?php

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
     * @dataProvider getDifferentMaxima
     * @param $max
     */
    public function testEncodeDecodeRandomNumbers($max)
    {
        list($prime, $inverse, $xor) = Energon::generate(null, $max);
        $optimus = new Optimus($prime, $inverse, $xor, $max);

        for ($i = 0; $i < 1000; $i++) {
            $value = rand(0, $max);

            $encoded = $optimus->encode($value);
            $decoded = $optimus->decode($encoded);

            $assertMsgDetails = sprintf(
                'Prime: %s, Inverse: %s, Xor: %s, Max: %s, Value: %s',
                $prime,
                $inverse,
                $xor,
                $max,
                $value
            );

            $this->assertEquals(
                $value,
                $decoded,
                "Encoded value $encoded has not decoded back to $value. ($assertMsgDetails)"
            );
            $this->assertNotEquals(
                $value,
                $encoded,
                "Encoded value $encoded matches the original value. ($assertMsgDetails)"
            );
        }
    }

    public function getDifferentMaxima()
    {
        return [
            [2147483647],
            [262143],
            [4294967295],
        ];
    }

    public function testEncodeStrings()
    {
        $this->assertEquals($this->optimus->encode(20), $this->optimus->encode('20'));
        $this->assertEquals($this->optimus->decode(1440713122), $this->optimus->decode('1440713122'));
    }

    public function testEncodeBadStrings()
    {
        $this->expectException('InvalidArgumentException');

        $this->optimus->encode('foo');
    }

    public function testDecodeBadStrings()
    {
        $this->expectException('InvalidArgumentException');

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
