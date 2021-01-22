<?php
/**
 * Project functions
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 */

/**
 * Aligns a key-value array of strings.
 *
 * @param array  $args
 * @param string $sep
 * @param string $prefix
 * @param string $suffix
 * @param bool   $mb
 *
 * @return string
 */
function align(array $args, string $sep = ': ', string $prefix = '', string $suffix = '', bool $mb = false): string
{
    [$result, $maxLength, $method] = ['', 0, $mb ? 'mb_strlen' : 'strlen'];
    foreach ($args as $key => $val) {
        if ($method($key) > $maxLength) {
            $maxLength = $method($key);
        }
    }
    foreach ($args as $key => $val) {
        $result .= $prefix.$key.str_repeat(' ', $maxLength - $method($key)).$sep.$val.$suffix.PHP_EOL;
    }
    return $result;
}

/**
 * Converts bytes into higher level units, to be easier to read.
 *
 * @param int|float $bytes
 * @param int       $round
 *
 * @return string
 */
function bytesShortener($bytes, int $round = 0): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $index = 0;
    while ($bytes > 1024) {
        $bytes /= 1024;
        if (++$index === 8)
            break;
    }
    if ($round !== 0) {
        $bytes = round($bytes, $round);
    }
    return "$bytes {$units[$index]}";
}

/**
 * Makes an absolute path, using an array of dir|file names.
 *
 * @param string ...$args
 *
 * @return string
 */
function makePath(string ...$args): string
{
    return implode(DIRECTORY_SEPARATOR, $args);
}

/**
 * Returns the number of the machine's CPU cores.
 *
 * @return int
 */
function getCpuCores(): int
{
    return (int) (
        PHP_OS_FAMILY === 'Windows'
            ? getenv('NUMBER_OF_PROCESSORS')
            : substr_count(file_get_contents('/proc/cpuinfo'), 'processor')
    );
}
