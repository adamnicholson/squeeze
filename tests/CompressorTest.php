<?php

namespace Adamnicholson\Squeeze;

class CompressorTest extends \PHPUnit_Framework_TestCase
{
    public function testCompress()
    {
        $rawFile = __DIR__ . '/raw.txt';
        $raw = file_get_contents($rawFile);

        $compressor = new Compressor();

        $compressedFile = tempnam(sys_get_temp_dir(), 'squeeze-test');
        $compressed = $compressor->compress($raw);
        file_put_contents($compressedFile, $compressed);

        $this->assertLessThan(filesize($rawFile), filesize($compressedFile));

        $this->assertEquals($raw, $compressor->decompresse($compressed));
    }
}
