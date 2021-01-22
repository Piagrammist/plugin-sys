<?php
/**
 * Ping-pong plugin
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

return function (array& $args) {
    if (preg_match('~^\W?ping$~i', $args['text'])) {
        $start = microtime(true) * 1000;
        $sent  = yield $this->messages->sendMessage([
            'peer'            => $args['update'],
            'message'         => '`Pong !`',
            'parse_mode'      => 'Markdown',
            'reply_to_msg_id' => $args['msg_id']
        ]);
        $ping = round(microtime(true) * 1000 - $start, 2);
        yield $this->sleep(2);
        yield $this->editMsg($args['update'], $sent['updates'][0]['id'] ?? $sent['id'], "`Pong took $ping ms.`", 'Markdown');
    }
};
