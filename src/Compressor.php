<?php

namespace Adamnicholson\Squeeze;

class Compressor
{
    public function compress(string $data): string
    {
        return str_replace('Vitae', '__V', $data);
    }

    public function decompresse(string $compressed): string
    {
        return str_replace('__V', 'Vitae', $compressed);
    }
}
