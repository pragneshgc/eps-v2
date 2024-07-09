<?php

namespace App\Helpers;

use App\Supports\XMLReader;
use Illuminate\Support\Str;
use App\Supports\JSONReader;

class Generic
{
    public static function getIP()
    {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = request()->ip();

        if (Str::contains($ipaddress, ':')) {
            $ip = explode(':', $ipaddress);
            $ipaddress = $ip[0];
        }

        return $ipaddress;
    }

    public static function readFile($content, $type): array
    {
        $reader = match ($type) {
            "application/xml" => new XMLReader($content),
            "application/json" => new JSONReader($content),
            default => new XMLReader($content)
        };

        if ($reader->validate()) {
            return [
                'data' => $reader->toArray(),
                'error' => []
            ];
        } else {
            return [
                'data' => [],
                'error' => $reader->getErrors()
            ];
        }
    }

    public static function matchLanguageMapping($needle, $language = true)
    {
        $map = [
            '1' => 'ENU',
            '2' => 'ENU',
            '15' => 'ENU',
            '58' => 'CES',
            '59' => 'DAN',
            '83' => 'DEU',
            '86' => 'ELL',
            '69' => 'EST',
            '74' => 'FIN',
            '75' => 'FRA',
            '106' => 'HEB',
            '99' => 'HUN',
            '107' => 'ITA',
            '119' => 'LAV',
            '125' => 'LIT',
            '152' => 'NLD',
            '162' => 'NOR',
            '172' => 'POL',
            '173' => 'POR',
            '177' => 'RON',
            '178' => 'RUS',
            '191' => 'SLK',
            '192' => 'SLV',
            '196' => 'SPE',
            '204' => 'SWE',
            '216' => 'TUR',
        ];

        foreach ($map as $key => $value) {
            if ((int) $key == $needle && $language) {
                return $value;
            } else if ($value == $needle && !$language) {
                return (int) $key;
            }
        }

        return $language ? 'ENU' : 1;
    }
}
