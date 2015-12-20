<?php

namespace Adamnicholson\Squeeze;

class Compressor
{
    private $usedKeys = [];

    public function compress(string $data, Progress $progress = null): string
    {
        $progress = $progress ?: new Progress();

        $words = array_unique(preg_split('/[\s,\.]+/', $data));
        $replacements = $this->replacePhrases($data, $words, $progress, 0);

        $data = str_replace(array_keys($replacements), $replacements, $data);

        $meta = http_build_query($replacements) . '&';

        return $meta . PHP_EOL . $data;
    }

    private function replacePhrases($data, $phrases, Progress $progress, float $progressModifier)
    {
        $replacements = [];

        $i = 0;
        foreach ($phrases as $phrase) {
            $i++;
            $progress->notify(($i / count($phrases)) + $progressModifier);

            if (!$phrase) {
                continue;
            }

            if (!strstr($data, $phrase)) {
                // This phrase isn't even in the data. Skip it
                continue;
            }

            $key = $this->findKey($data);

            if (strlen($phrase) <= strlen($key)) {
                // Do not bother if the key is as big as the word
                $this->freeKey($key);
                continue;
            }

            $usages = substr_count($data, $phrase);

            $usedSpace =  $usages * strlen($phrase);
            $willUse = $usages * strlen($key) + strlen($key) + strlen($phrase) + 1;
            if ($willUse >= $usedSpace) {
                // Do not bother if the extra meta data means its overall larger with replacement
                $this->freeKey($key);
                continue;
            }

            $replacements[$phrase] = $key;
        }

        return $replacements;
    }

    public function decompress(string $compressed): string
    {
        preg_match('/^.+\n/', $compressed, $meta);

        $replacements = [];
        foreach (explode('&', $meta[0]) as $chunk) {
            $param = explode('=', $chunk);
            if (count($param) !== 2) {
                continue;
            }
            $replacements[urldecode($param[0])] = urldecode($param[1]);
        }

        $body = preg_replace('/^.*\n/', '', $compressed);

        $body = str_replace(array_values($replacements), array_keys($replacements), $body);

        return $body;
    }

    private function findKey(string $data): string
    {
        foreach (array_merge(range('a', 'z'), range('A', 'Z')) as $i) {
            if (!strstr($data, $i) && !in_array($i, $this->usedKeys)) {
                $this->usedKeys[$i] = $i;
                return $i;
            }
        }

        $i = 0;
        $literal = '_' .$i . '_';
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
