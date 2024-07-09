<?php

namespace App\Library;

/**
 * General helper methods for various parts of the application
 * Should be refactored at one point
 */
class Helper
{
    /**
     * Get headers for the CSV manual print endpoints
     *
     * @param int $id
     * @return array
     */
    public function manualPrintHeaders($id)
    {
        return [
            "Content-Type" => "data:text/csv;charset=utf-8,\uFEFF",
            "Content-Disposition" => "attachment; filename=$id.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];
    }

    /**
     * Round a date time value to the nearest number
     *
     * @return string
     */
    public function round()
    {
        return date('H:i', floor(time() / (5 * 60)) * (5 * 60));
    }

    /**
     * Remove everything except numericals from a phone number
     *
     * @param string $n
     * @return string
     */
    public function formatPhone($n)
    {
        return preg_replace('/[^0-9]/', '', $n);
    }

    /**
     * Undocumented function
     *
     * @param [type] $string
     * @return boolean
     */
    public function hasNumber($string)
    {
        return preg_match('~[0-9]~', $string);
    }

    /**
     * Undocumented function
     *
     * @param [type] $address1
     * @param [type] $address2
     * @param [type] $address3
     * @param [type] $address4
     * @param [type] $ups
     * @return void
     */
    public function cleanAddresses($address1, $address2, $address3, $address4, $ups)
    {
        if ($address2 != "" && is_numeric($address1) && !is_numeric($address2)) {
            $address1 = $address1 . " " . $address2;
            $address2 = $address3;
            $address3 = $address4;
        } else if ($address2 != "" && !is_numeric($address1) && is_numeric($address2)) {
            $address1 = $address2 . " " . $address1;
            $address2 = $address3;
            $address3 = $address4;
        };

        if (!$ups && $address2 == "") {
            $address2 = $address3;
            $address3 = $address4;
        };

        if (!$ups && $address2 == "" && $address4 == "" && $address3 != "") {
            $address2 = $address2;
            $address3 = $address3;
        };

        if ($address2 == "" && $address3 == "" && $address4 != "") {
            $address2 = $address2;
            $address3 = $address4;
            $address4 = $address3;
        };

        if (!$ups && $address3 == "") {
            $address3 = $address4;
        };

        if ($ups && $address4 == "" && $address3 != "") {
            $address4 = $address3;
        };

        return [
            'address1' => str_replace(',', '', $address1), 'address2' => str_replace(',', '', $address2),
            'address3' => str_replace(',', '', $address3), 'address4' => str_replace(',', '', $address4)
        ];
    }
}
