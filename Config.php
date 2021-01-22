<?php
/**
 * Configuration class.
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 */

class Config
{
    /**
     * Telegram username of the robot admin.
     * <br><br>
     * Note: pass <b>me</b> if you are running the robot as a self-bot !
     *
     * @var string
     */
    public const ADMIN = "me";

    /**
     * Info of an app that registered on my.telegram.
     *
     * @see https://my.telegram.org
     * @var array
     */
    public const APP_INFO = [
        'api_id'   => null,
        'api_hash' => null
    ];

    /**
     * The name of the MadelineProto log file.
     *
     * @var string
     */
    public const LOGS_NAME = 'MadelineProto.log';

    /**
     * The name of the MadelineProto session file.
     *
     * @var string
     */
    public const SESSION_NAME = 'bot.session';

    /**
     * Path to the plugins directory.
     *
     * @var string
     */
    public const PLUGINS_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'plugins';
}
