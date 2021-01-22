<?php
/**
 * Start plugin (An example for bot-API)
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

return function (array& $args) {
    if (self::$isBot && preg_match('~^/start ?(.+)?$~i', $args['text'], $match)) {
        if (isset($match[1])) {
            yield $this->sendMsg($args['update'], "Hello, Welcome!\nStart param: {$match[1]}", null, $args['msg_id']);
        } else {
            yield $this->sendMsg($args['update'], "Hello, Welcome!", null, $args['msg_id']);
        }
    }
};
