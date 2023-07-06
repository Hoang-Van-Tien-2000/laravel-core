<?php

use Carbon\Carbon;
use Core\Providers\Facades\Storages\BaseStorage;

if (!function_exists('randomString')) {
    function randomString($length = 10, $isText = true, $isUppercase = true)
    {
        $characters = '0123456789';

        if ($isText) {
            $characters .= 'abcdefghijklmnopqrstuvwxyz';
        }

        if ($isUppercase) {
            $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('is_multi_array')) {

    function is_multi_array($arr)
    {
        rsort($arr);
        return isset($arr[0]) && is_array($arr[0]);
    }
}

if (!function_exists('getFileUrl')) {

    function getFileUrl($url)
    {
        return BaseStorage::url($url);
    }
}

if (!function_exists('getDateFormat')) {

    function getDateFormat($value)
    {
        return ($value && $value != '0000-00-00') ? Carbon::parse($value)->format('Y年m月d日') : null;
    }
}

if (!function_exists('getDateTimeFormat')) {

    function getDateTimeFormat($value)
    {
        return ($value && $value != '0000-00-00 00:00:00') ? Carbon::parse($value)->format('Y年m月d日 H:i:s') : null;
    }
}

if (!function_exists('getMonthYearForm')) {
    function getMonthYearForm($inputArray = [])
    {
        if (!is_array($inputArray)) {
            return [];
        }

        $numberArray = array_map(function ($value) {
            return str_pad($value, 2, '0', STR_PAD_LEFT);
        }, $inputArray);

        return array_combine($numberArray, $numberArray);
    }
}

if (!function_exists('convertSJISToUtf8')) {

    function convertSJISToUtf8($str)
    {
        if (mb_detect_encoding($str, mb_detect_order(), true) == 'UTF-8') {
            return $str;
        }
        return mb_convert_encoding($str, "UTF-8", getConfig('convert_encoding'));
    }
}

if (!function_exists('isRoute')) {

    function isRoute($name)
    {
        return in_array($name, explode('.', \Illuminate\Support\Facades\Route::currentRouteName()));
    }
}

if (!function_exists('getOriginalMemberId')) {

    function getOriginalMemberId($memberId)
    {
        return str_replace(getConfig('gmo.prefix_member_id'), "", $memberId);
    }
}

if (!function_exists('getOriginalOrderId')) {

    function getOriginalOrderId($orderID)
    {
        return str_replace(getConfig('gmo.prefix_order_id'), "", $orderID);
    }
}

if (!function_exists('getUrl')) {
    function getUrl($url)
    {
        if (getConfig('pdf.is_public_path')) {
            return public_path($url);
        }
        return public_url($url);
    }
}
