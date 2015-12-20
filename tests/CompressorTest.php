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

        $this->output('Compressing');
        $compressed = $compressor->compress($raw, (new Progress())->listen(function ($percentage) {
            static $lastBand = 0;

            $thisBand = floor($percentage * 10) / 10;

            if ($thisBand > $lastBand) {
                $this->output(round($thisBand * 100) . '%');
            }

            $lastBand = $thisBand;
        }));

        file_put_contents($compressedFile, $compressed);

        $this->assertLessThan(filesize($rawFile), filesize($compressedFile));

        $this->output('Compression ration ' . filesize($compressedFile) . '/' . filesize($rawFile) . ' = ' . round(filesize($compressedFile) / filesize($rawFile), 5) . '%');
        $this->output('Decompressing');
        $this->assertEquals($raw, $compressor->decompress($compressed));
    }

    private function output(string $message)
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}
