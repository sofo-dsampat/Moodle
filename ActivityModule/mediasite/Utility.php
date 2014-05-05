<?php

namespace Sonicfoundry;

function EndsWith($str, $needle)
{
    $length = strlen($needle);
    return !$length || substr($str, - $length) === $needle;
}
function StartsWith($str, $needle)
{
    return substr($str, 0, strlen($needle)) === $needle;
}
function substr_unicode($str, $s, $l = null)
{
    return join("", array_slice(
        preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
}
