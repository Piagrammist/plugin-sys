<?php
/**
 * Restart plugin
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

return function (array& $args) {
    if (preg_match('~^\W?(?:re|restart)$~i', $args['text'])) {
        if (!self::$isCli) {
            yield $this->sendMsg($args['update'], "`Robot was restarted.`", 'Markdown', $args['msg_id']);
            $this->restart();
        } else {
            yield $this->sendMsg($args['update'], "`This command just works on web-server !`", 'Markdown', $args['msg_id']);
        }
    }
};
