<?php
/**
 * Update dumper plugin (Just for development time)
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

return function (array& $args) {
    if ($args['text'] === '{}') {
        if ($args['reply_id']) {
            $message = yield $this->
                {$this->getChatType($args['update']) === 'channel' ? 'channels' : 'messages'}
                    ->getMessages(['channel' => $args['update'], 'id' => [$args['reply_id']]]);
            yield $this->sendMsg($args['update'], self::objToStr($message['messages'][0], true), 'HTML', $args['msg_id']);
        } else {
            yield $this->sendMsg($args['update'], self::objToStr($args['update'], true), 'HTML', $args['msg_id']);
        }
    }
};
