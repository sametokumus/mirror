<?php

use App\Models\CurrencyLog;
use Carbon\Carbon;

if (! function_exists('convertUSDtoTRY')) {
    function convertUSDtoTRY($price)
    {
//        $today = date('Y-m-d');
//        $yesterday = date('Y-m-d',strtotime($today)-1);
//        $currency = CurrencyLog::query()->where('day', $yesterday)->first()->dollar;
        $currency = CurrencyLog::query()->orderBy('id', 'desc')->first()->dollar;
        return number_format($price * $currency, 2,".","");
    }
}

if (! function_exists('convertEURtoTRY')) {
    function convertEURtoTRY($price)
    {
//        $today = date('Y-m-d');
//        $yesterday = date('Y-m-d',strtotime($today)-1);
//        $currency = CurrencyLog::query()->where('day', $yesterday)->first()->euro;
        $currency = CurrencyLog::query()->orderBy('id', 'desc')->first()->euro;
        return number_format($price * $currency, 2,".","");
    }
}
