<?php

namespace {

    use Jenssegers\Optimus\Energon;
    use Jenssegers\Optimus\Optimus;

    class OptimusTest extends PHPUnit_Framework_TestCase
    {
        /**
         * @var Optimus
         */
        private $optimus;

        public static $gmpExtensionLoaded;

        public function setUp()
        {
            list($prime, $inverse, $xor) = Energon::generate();
            $this->optimus = new Optimus($prime, $inverse, $xor);
        }

        public function tearDown()
        {
            self::$gmpExtensionLoaded = null;
        }

        /**
         * @dataProvider getPrimesTestData
         * @param $prime
         * @param $inverse
         * @param $xor
         * @param $bitLength
         * @param $value
         */
        public function testEncodeDecode($prime, $inverse, $xor, $bitLength, $value)
        {
            $optimus = new Optimus($prime, $inverse, $xor, $bitLength);

            $encoded = $optimus->encode($value);
            $decoded = $optimus->decode($encoded);

            $assertMsgDetails = sprintf(
                'Prime: %s, Inverse: %s, Xor: %s, Bit length: %s, Value: %s',
                $prime,
                $inverse,
                $xor,
                $bitLength,
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
            $bitLength31 = 31;
            $bitlength32 = 32;
            $bitLength24 = 24;

            $bitLengths = [
                $bitLength31,
                $bitlength32,
                $bitLength24
            ];

            $randXor = 873691988;
            $smlPrime = 10000019;

            $lrgPrimes = [
                $bitLength31 => 2147483647,
                $bitlength32 => 4294967291,
                $bitLength24 => 999999967,
            ];

            $smlPrimeInverses = [
                $bitLength31 => Energon::calculateInverse($smlPrime, $bitLength31),
                $bitlength32 => Energon::calculateInverse($smlPrime, $bitlength32),
                $bitLength24 => Energon::calculateInverse($smlPrime, $bitLength24)
            ];

            $lrgPrimeInverses = [
                $bitLength31 => Energon::calculateInverse($lrgPrimes[$bitLength31], $bitLength31),
                $bitlength32 => Energon::calculateInverse($lrgPrimes[$bitlength32], $bitlength32),
                $bitLength24 => Energon::calculateInverse($lrgPrimes[$bitLength24], $bitLength24)
            ];

            $testData = [];

            foreach ($bitLengths as $bitLength) {
                $testData = array_merge(
                    $testData,
                    [
                        [$smlPrime, $smlPrimeInverses[$bitLength], 0, $bitLength, 1],
                        [$smlPrime, $smlPrimeInverses[$bitLength], 0, $bitLength, $bitLength],
                        [$smlPrime, $smlPrimeInverses[$bitLength], $randXor, $bitLength, 1],
                        [$smlPrime, $smlPrimeInverses[$bitLength], $randXor, $bitLength, $bitLength],
                        [$lrgPrimes[$bitLength], $lrgPrimeInverses[$bitLength], 0, $bitLength, 1],
                        [$lrgPrimes[$bitLength], $lrgPrimeInverses[$bitLength], 0, $bitLength, $bitLength],
                        [$lrgPrimes[$bitLength], $lrgPrimeInverses[$bitLength], $randXor, $bitLength, 1],
                        [$lrgPrimes[$bitLength], $lrgPrimeInverses[$bitLength], $randXor, $bitLength, $bitLength],
                    ]
                );
            }

            return $testData;
        }

        /**
         * @dataProvider getBitLengthTestData
         * @param $bitLength
         */
        public function testEncodeDecodeRandomNumbers($bitLength)
        {
            $maxInt = pow(2, $bitLength) - 1;
            list($prime, $inverse, $xor) = Energon::generate(null, $bitLength);

            $optimus = new Optimus($prime, $inverse, $xor, $bitLength);

            for ($i = 0; $i < 1000; $i++) {
                $id = rand(0, $maxInt);

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

        public function getBitLengthTestData()
        {
            return [
                [31],
                [62],
                [48],
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

        public function testExceptionIfGmpRequiredButNotLoaded()
        {
            $this->setExpectedException('RuntimeException');

            self::$gmpExtensionLoaded = false;
            $lrgBitLength = 32; // GMP is required for 32bit numbers.
            $prime = 10000019;

            new Optimus($prime, Energon::calculateInverse($prime, $lrgBitLength), 0, $lrgBitLength);
        }
    }
}

namespace Jenssegers\Optimus {

    function extension_loaded($name) {
        return \OptimusTest::$gmpExtensionLoaded === null ? \extension_loaded($name) : \OptimusTest::$gmpExtensionLoaded;
    }
}
