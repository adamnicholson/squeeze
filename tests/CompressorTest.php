<?php

namespace Adamnicholson\Squeeze;

class CompressorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $rawFile
     * @dataProvider testFiles
     */
    public function testCompress(string $rawFile)
    {
        $raw = file_get_contents($rawFile);

        $compressor = new Compressor();

        $compressedFile = tempnam(sys_get_temp_dir(), 'squeeze-test-' . date('His') . '-compressed');
        $decompressedFile = tempnam(sys_get_temp_dir(), 'squeeze-test-' . date('His') . '-decompressed');

        $this->output('Compressing ' . $rawFile);
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

        $this->output('Compression ration ' . filesize($compressedFile) . '/' . filesize($rawFile) . ' = ' . round(filesize($compressedFile) / filesize($rawFile), 5) * 100 . '%');
        $this->output('Decompressing');
        $decompressed = $compressor->decompress($compressed);
        file_put_contents($decompressedFile, $decompressed);
        $this->assertEquals($raw, $decompressed);
    }

    private function output(string $message)
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    public function testFiles()
    {
        return [
            [__DIR__ . '/raw.txt'],
            [__DIR__ . '/raw2.txt'],
            [__DIR__ . '/raw3.txt'],
            [__DIR__ . '/raw4.txt'],
        ];
    }
}
