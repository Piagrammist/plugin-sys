<?php
/**
 * Template for MadelineProto (user-)bot development
 *   - Async
 *   - Plugin based
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 */

use danog\MadelineProto\API;
use danog\MadelineProto\Logger;

require 'functions.php';
require 'loader.php';
require 'Config.php';
require 'EventHandler.php';

$client = new API(
    makePath(__DIR__, Config::SESSION_NAME),
    [
        'app_info' => Config::APP_INFO,
        'logger'   => [
            'max_size'     => 1 * 1024 * 1024,
            'logger'       => Logger::FILE_LOGGER,
            'logger_level' => Logger::ULTRA_VERBOSE,
            'logger_param' => makePath(__DIR__, Config::LOGS_NAME)
        ]
    ]
);
$client->async(true);
$client->loop(function () use ($client): Generator {
    yield $client->start();
    yield $client->setEventHandler(EventHandler::class);
});
$client->loop();
