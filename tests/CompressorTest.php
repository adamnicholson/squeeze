<?php

namespace Adamnicholson\Squeeze;

class CompressorTest extends \PHPUnit_Framework_TestCase
{
    public function testCompress()
    {
        $raw = file_get_contents(__DIR__ . '/raw.txt');

        $compressor = new Compressor();

        $compressed = $compressor->compress($raw);

        $this->assertLessThan(strlen($raw), strlen($compressed));

        $decompressed = $compressor->decompresse($compressed);

        $this->assertEquals($raw, $decompressed);
    }
}
