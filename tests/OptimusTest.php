<?php

use Jenssegers\Optimus\Optimus;

class OptimusTest extends PHPUnit_Framework_TestCase {

    public function testEncodeWithXor()
    {
        $optimus = new Optimus(1580030173, 59260789, 200462719);

        $encoded = $optimus->encode(1);

        $this->assertEquals(1440713122, $encoded);
    }

    public function testDecodeWithXor()
    {
        $optimus = new Optimus(1580030173, 59260789, 200462719);

        $decoded = $optimus->decode(1440713122);

        $this->assertEquals(1, $decoded);
    }

    public function testEncodeWithoutXor()
    {
        $optimus = new Optimus(1580030173, 59260789);

        $encoded = $optimus->encode(1);

        $this->assertEquals(1580030173, $encoded);
    }

    public function testDecodeWithoutXor()
    {
        $optimus = new Optimus(1580030173, 59260789);

        $decoded = $optimus->decode(1580030173);

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

}
