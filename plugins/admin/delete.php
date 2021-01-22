<?php
/**
 * Delayed delete plugin
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

return function (array& $args) {
    if (preg_match('~^\W?delete|del (\d+)$~i', $args['text'], $match)) {
        $delay = (int) $match[1];
        if (121 > $delay && $delay > 0) {
            $message_to_be_deleted = $args['msg_id'];
            if ($args['reply_id']) {
                yield $this->delMsg($args['update'], [$args['msg_id']]);
                $message_to_be_deleted = $args['reply_id'];
            }
            $sent = yield $this->messages->sendMessage([
                'peer'            => $args['update'],
                'message'         => "`Message will be deleted after $delay seconds ...`",
                'parse_mode'      => 'Markdown',
                'reply_to_msg_id' => $message_to_be_deleted
            ]);
            yield $this->delMsg(
                $args['update'],
                [$message_to_be_deleted, $sent['updates'][0]['id'] ?? $sent['id']],
                $delay
            );
        } else {
            yield $this->sendMsg(
                $args['update'],
                "`Please just enter the second in range of 1-120 !`",
                'Markdown',
                $args['msg_id']
            );
        }
    }
};
