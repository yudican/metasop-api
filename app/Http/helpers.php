<?php

if (!function_exists('getSignature')) {
  function getSignature($type)
  {
    return md5(env('DIGIFLAZZ_USERNAME') . env('DIGIFLAZZ_API_KEY') . $type);
  }
}

// extract number from string
if (!function_exists('extractNumberFromString')) {
  function extractNumberFromString($text)
  {
    return preg_replace('/[^0-9]/', '', $text);
  }
}
