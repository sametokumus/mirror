<?php

use App\Models\CurrencyLog;
use Carbon\Carbon;

if (! function_exists('convertUSDtoTRY')) {
    function convertUSDtoTRY($price)
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d',strtotime($today)-1);
        $currency = CurrencyLog::query()->where('day', $yesterday)->first()->dollar;
        return $price * $currency;
    }
}

if (! function_exists('convertEURtoTRY')) {
    function convertEURtoTRY($price)
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d',strtotime($today)-1);
        $currency = CurrencyLog::query()->where('day', $yesterday)->first()->euro;
        return $price * $currency;
    }
}
