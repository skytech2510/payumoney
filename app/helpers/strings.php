<?php 


if (!function_exists('convertToBytes')) {
    /**
     * Converts a string to a string of equal bytes. Supports up to Petabyte.
     * PHP allows shortcuts for byte values, including K (kilo), M (mega) and G (giga) etc.
     *
     * Example input: '64M', '1G', '24M'.
     * Example output: '1024'
     *
     * @param string $from
     * @return string|null
     */
    function convertToBytes($from)
    {
        $units = ['B', 'K', 'M', 'G', 'T', 'P'];
        $number = substr($from, 0, -1);

        $suffix = strtoupper(substr($from, -1));
        $exponent = array_flip($units)[$suffix] ?? null;

        if ($exponent === null) {
            return null;
        }

        return strval($number * (1024 ** $exponent));
    }
}

if (!function_exists('convertBytesToOtherUnit')) {
    /**
     * Converts a bytes string to specified unit byte.
     * Supports up to Petabyte & skips decimal points
     *
     * Example output: '64M', '1G', '24M'.
     *
     * @param string $bytes
     * @param string $unit
     * @return string
     */
    function convertBytesToOtherUnit($bytes, $unit = 'M')
    {
        $units = ['B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5];
        $value = 0;

        if ($bytes > 0) {
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes) / log(1024));
                $unit = array_search($pow, $units);
            }
            $value = ($bytes / pow(1024, floor($units[$unit])));
        }

        return sprintf('%d' . $unit, $value);
    }
}