<?php

use Jenssegers\Optimus\Optimus;

class OptimusTest extends PHPUnit_Framework_TestCase {

    public function testEncodeDecodeWithXor()
    {
        $optimus = new Optimus(1580030173, 59260789, 200462719);

        $encoded = $optimus->encode(1);
        $decoded = $optimus->decode($encoded);

        $this->assertNotEquals(1, $encoded);
        $this->assertNotEquals($encoded, $decoded);
        $this->assertEquals(1, $decoded);
    }

    public function testEncodeDecodeWithoutXor()
    {
        $optimus = new Optimus(1580030173, 59260789);

        $encoded = $optimus->encode(1);
        $decoded = $optimus->decode(1580030173);

        $this->assertEquals(1580030173, $encoded);
        $this->assertNotEquals(1, $encoded);
        $this->assertNotEquals($encoded, $decoded);
        $this->assertEquals(1, $decoded);
    }

    public function testEncodeDecodeRandomNumbers()
    {
        $optimus = new Optimus(1580030173, 59260789);

        for ($i = 0; $i < 20; $i++)
        {
            $id = rand();
            $encoded = $optimus->encode($id);
            $decoded = $optimus->decode($encoded);

            $this->assertEquals($id, $decoded);
            $this->assertNotEquals($id, $encoded);
        }
    }

    public function testEncodeStrings()
    {
        $optimus = new Optimus(1580030173, 59260789);

        $this->assertEquals($optimus->encode(20), $optimus->encode("20"));
        $this->assertEquals($optimus->decode(1440713122), $optimus->decode("1440713122"));
    }

    public function testEncodeBadStrings()
    {
        $this->setExpectedException('InvalidArgumentException');

        $optimus = new Optimus(1580030173, 59260789);

        $optimus->encode("foo");
    }

}
