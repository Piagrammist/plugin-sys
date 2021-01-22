<?php
/**
 * Robot's statistics plugin
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 *
 * @param array $args
 *
 * @return Generator|void
 */

use danog\MadelineProto\Logger;
use function Amp\File\size;

return function (array& $args) {
    if (preg_match('~^\W?statu?s?$~i', $args['text'])) {
        if (!self::$isBot) {
            $chats = ['bot' => 0, 'user' => 0, 'chat' => 0, 'channel' => 0, 'supergroup' => 0];
            foreach (yield $this->getDialogs() as $dialog) {
                try {
                    $chats[yield $this->getInfo($dialog)['type']]++;
                } catch (Throwable $e) {
                    $this->logger($e, Logger::ERROR);
                }
            }
            $contacts = yield $this->contacts->getContacts()['contacts'];
            $mutual = 0;
            foreach ($contacts as $contact) {
                if ($contact['mutual']) {
                    $mutual++;
                }
            }
            $userStats =
                "**Chats**\n"
                .align(
                    [
                        'Private'        => $chats['user'],
                        'Contact'        => count($contacts),
                        'Mutual Contact' => $mutual,
                        'Group'          => $chats['chat'],
                        'Supergroup'     => $chats['supergroup'],
                        'Channel'        => $chats['channel'],
                        'Bot'            => $chats['bot']
                    ],
                    ': ',
                    '`• ',
                    '`'
                )
                ."\n";
        }
        $serverStats =
            "**Server**\n"
            .align(
                [
                    'CPU cores'                  => getCpuCores(),
                    'Robot mem usage'            => bytesShortener(memory_get_usage()         , 2),
                    'Robot max mem usage'        => bytesShortener(memory_get_peak_usage()    , 2),
                    'Allocated mem from sys'     => bytesShortener(memory_get_usage(true)     , 2),
                    'Max allocated mem from sys' => bytesShortener(memory_get_peak_usage(true), 2),
                    'Session size'               => bytesShortener(yield size(makePath(__DIR__, '..', '..', Config::SESSION_NAME)), 2),
                    'Logs size'                  => bytesShortener(yield size(makePath(__DIR__, '..', '..', Config::LOGS_NAME))   , 2),
                    'PHP version'                => PHP_VERSION
                ],
                ': ',
                '`• ',
                '`'
            );
        yield $this->sendMsg(
            $args['update'],
            "**Robot Statistics**\n\n" . ($userStats ?? '') . $serverStats,
            'Markdown',
            $args['msg_id']
        );
    }
};
