<?php
/**
 * MadelineProto (installer &) loader
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 */

if (file_exists(makePath(__DIR__, 'vendor', 'autoload.php'))) {
    require makePath(__DIR__, 'vendor', 'autoload.php');
} else {
    /*
     * Don't use absolute path for madeline.php!
     * madeline.phar doesn't use abs path. So, they will be craeted in different folders.
     */
    if (!file_exists('madeline.php')) {
        $oldLimit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');
        copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
        ini_set('memory_limit', $oldLimit);
    }
    require 'madeline.php';
}
