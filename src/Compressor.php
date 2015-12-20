<?php

namespace Adamnicholson\Squeeze;

class Compressor
{
    private $usedKeys = [];

    public function compress(string $data, Progress $progress = null): string
    {
        $progress = $progress ?: new Progress();

        $replacements = [];

        preg_match_all('/\s([a-zA-Z0-0]+)\s/', $data, $matches);

        $words = array_unique($matches[1]);

        $i = 0;
        foreach ($words as $word) {
            $i++;
            $progress->notify($i / count($words));

            $key = $this->findKey($data);

            if (strlen($word) <= strlen($key)) {
                // Do not bother if the key is as big as the word
                $this->freeKey($key);
                continue;
            }

            $usages = substr_count($data, $word);

            $usedSpace =  $usages * strlen($word);
            $willUse = $usages * strlen($key) + strlen($key) + strlen($word) + 1;
            if ($willUse >= $usedSpace) {
                // Do not bother if the extra meta data means its overall larger with replacement
                $this->freeKey($key);
                continue;
            }

            $replacements[$word] = $key;
        }

        //@ todo Not sure why it fails if we don't have a final random replace on the end
        $replacements[")(*&"] = $this->findKey($data);

        $data = str_replace(array_keys($replacements), $replacements, $data);

        $meta = http_build_query($replacements);

        return $meta . PHP_EOL . $data;
    }

    public function decompress(string $compressed): string
    {
        preg_match('/^.+\n/', $compressed, $meta);

        parse_str($meta[0], $replacements);

        $body = preg_replace('/^.+\n/', '', $compressed);

        $body = str_replace(array_values($replacements), array_keys($replacements), $body);

        return $body;
    }

    private function findKey(string $data): string
    {
        $i = 0;
        $literal = '_' . $i . '_';
        while (strstr($data, $literal) || in_array($literal, $this->usedKeys)) {
            $literal = '_' . $i . '_';
            $i++;
        }

        $this->usedKeys[$literal] = $literal;

        return $literal;
    }

    private function freeKey(string $key)
    {
        unset($this->usedKeys[$key]);
    }
}
