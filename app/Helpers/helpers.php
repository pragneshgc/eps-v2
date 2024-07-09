<?php

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

if (!function_exists('successResponse')) {
    function successResponse(array $data, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse(?array $data = [], string $message = '', int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $data ?? [],
            'message' => $message,
        ], $code);
    }
}

if (!function_exists('replaceNullValueWithEmptyString')) {
    function replaceNullValueWithEmptyString(&$value)
    {
        $value = $value === null ? "" : $value;
    }
}

if (!function_exists('convertNullToEmptyString')) {
    /**
     * Convert null to '' in a array
     *
     * @param array $arr
     * @return array
     */
    function convertNullToEmptyString(array $arr): array
    {
        array_walk_recursive($arr, "replaceNullValueWithEmptyString");
        return $arr;
    }
}

if (!function_exists('replaceEmptyArrWithEmptyString')) {
    function replaceEmptyArrWithEmptyString(&$value)
    {
        $value = empty($value) ? "" : $value;
    }
}

if (!function_exists('convertEmptyArrToEmptyString')) {
    /**
     * Convert [] to '' in a array
     *
     * @param array $arr
     * @return array
     */
    function convertEmptyArrToEmptyString(array $arr): array
    {
        $newArr = array();
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                unset($arr[$key]);

                //Is it an empty array, make it a string
                if (empty($value)) {
                    $newArr[$key] = '';
                } else {
                    $newArr[$key] = convertEmptyArrToEmptyString($value);
                }
            } else {
                $newArr[$key] = $value;
            }
        }
        return $newArr;
    }
}

if (!function_exists('convertTimestamp')) {
    /**
     * convert timestamp to current timezone format
     *
     * @param string $timestamp
     * @param string $format
     * @return string
     */
    function convertTimestamp(string $timestamp, string $format): string
    {
        return Carbon::createFromTimestamp($timestamp, config('app.timezone'))->format($format);
    }
}

if (!function_exists('isAzureStorageEnabled')) {
    /**
     * Check if azure storage enabled or not
     *
     * @return boolean
     */
    function isAzureStorageEnabled()
    {
        return config('filesystems.disks.azure.key') != '' ? true : false;
    }
}
