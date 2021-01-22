<?php
/**
 * Reload plugin
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

return function (array& $args) {
    if (preg_match('~^\W?reload$~i', $args['text'])) {
        $this->onStart();
        yield $this->sendMsg($args['update'], "`Plugins list reloaded.`", 'Markdown', $args['msg_id']);
    }
};
