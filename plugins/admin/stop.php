<?php
/**
 * Stop plugin
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return void
 */

return function (array& $args) {
    if (preg_match('~^\W?stop|die$~i', $args['text'], $match)) {
        $this->messages->sendMessage(
            ['peer'            => $args['update'],
             'message'         => "`Robot was stopped.`",
             'parse_mode'      => 'Markdown',
             'reply_to_msg_id' => $args['msg_id']],
            ['async' => false]
        );
        $this->shut();
    }
};
