<?php
/*
 * Copyright (c) 2024. Bennet Becker <dev@bennet.cc>
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace bennetcc\imap_apppasswd;
use bennetcc\Log;

const IMAP_APPPW_EDIT_BTN = <<<EOT
    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24">
        <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 
                    56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
    </svg>
EOT;
function __(string $val): string
{
    return IMAP_APPPW_PREFIX . "_" . $val;
}

/**
 * @throws \Random\RandomException
 * @throws \InvalidArgumentException
 */
function random_from_alphabet(int $len, string|array $alphabet, Log | null $logger = null): string {
    if ($len < 1) {
        throw new \InvalidArgumentException("$len is less than or equal to 0");
    }
    if (is_string($alphabet)) {
        $alphabet = str_split($alphabet);
    }
    $random_bytes = random_bytes($len);

    $logger?->trace($alphabet, bin2hex($random_bytes));

    return implode(array_map(function ($byte) use ($alphabet) {
        $asize = count($alphabet);
        $i = intval(round(ord($byte) / ((2.0 ** 8.0) / floatval($asize)))) % $asize;
        return $alphabet[$i];
    }, str_split($random_bytes)));
}

function chunk(#[\SensitiveParameter] string $str, int $len, $delimiter = "-"): string {
    return implode($delimiter, str_split($str, $len));
}