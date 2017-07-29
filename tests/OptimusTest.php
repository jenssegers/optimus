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

    public function testEncodeDecodeWithXor()
    {
        $encoded = $this->optimus->encode(1);
        $decoded = $this->optimus->decode($encoded);

        $this->assertNotEquals(1, $encoded);
        $this->assertNotEquals($encoded, $decoded);
        $this->assertEquals(1, $decoded);
    }

    public function testEncodeDecodeWithoutXor()
    {
        list($prime, $inverse, $xor) = Energon::generate();
        $optimus = new Optimus($prime, $inverse);
        $optimus->setMode(Optimus::MODE_NATIVE);

        $encoded = $optimus->encode(1);
        $decoded = $optimus->decode($encoded);

        $this->assertNotEquals(1, $encoded);
        $this->assertNotEquals($encoded, $decoded);
        $this->assertEquals(1, $decoded);
    }

    public function testEncodeDecodeRandomNumbers()
    {
        for ($i = 0; $i < 1000; $i++) {
            $id = rand(0, Optimus::DEFAULT_MAX_INT);
            $encoded = $this->optimus->encode($id);
            $decoded = $this->optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertNotEquals($id, $encoded);
        }
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

    public function testSmallerMax()
    {
        for ($i = 0; $i < 1000; $i++) {
            $max = pow(2, rand(2, 31)) - 1;
            $id = rand(0, $max);

            list($prime, $inverse, $xor) = Energon::generateMax($max);

            $optimus = new Optimus($prime, $inverse, $xor, $max);

            $encoded = $optimus->encode($id);
            $decoded = $optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertTrue($encoded <= $max);
        }
    }

    public function testGmpMode()
    {
        $this->optimus->setMode(Optimus::MODE_GMP);

        for ($i = 0; $i < 1000; $i++) {
            $id = rand(0, Optimus::DEFAULT_MAX_INT);
            $encoded = $this->optimus->encode($id);
            $decoded = $this->optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertNotEquals($id, $encoded);
        }
    }
}
