<?php
/**
 * EventHandler class.
 *
 * @author  Piagrammist <https://github.com/Piagrammist>
 * @license <https://opensource.org/licenses/MIT>
 */

use danog\MadelineProto\Magic;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Shutdown;
use danog\MadelineProto\Exception;
use danog\MadelineProto\EventHandler as MadelineProtoEventHandler;

use function Amp\File\isdir;
use function Amp\File\isfile;
use function Amp\File\scandir;

final class EventHandler extends MadelineProtoEventHandler
{
    /**
     * Admin's Id.
     *
     * @var int
     */
    public static int $adminId;

    /**
     * Whether robot is a bot.
     *
     * @var bool
     */
    public static bool $isBot;

    /**
     * List of plugins closures.
     *
     * @var array
     */
    private static array $plugins;

    /**
     * Whether script is running through CLI.
     *
     * @var bool
     */
    public static bool $isCli;

    /**
     * Fetches admins's Id and loads plugins.
     *
     * @return Generator
     */
    public function onStart(): Generator
    {
        self::$isCli = in_array(PHP_SAPI, ['cli', 'cli-server', 'phpdbg'], true);

        try {
            $self = yield $this->getSelf();
            self::$isBot = $self['bot'] ?? false;
            if (Config::ADMIN === 'me') {
                if (self::$isBot) {
                    $this->logger(new Exception("You cannot declare 'me' as admin when you are running robot as a bot! Exiting ..."), Logger::ERROR);
                    $this->shut();
                }
                self::$adminId = $self['id'];
            } else {
                self::$adminId = yield $this->getInfo(Config::ADMIN)['User']['id'];
            }
        } catch (Throwable $e) {
            $this->logger($e, Logger::ERROR);
            $this->shut();
        }

        self::$plugins = ['public' => []];
        $pluginsCount  = 0;
        foreach (yield scandir(Config::PLUGINS_DIR) as $plugin) {
            $absPlugin = makePath(Config::PLUGINS_DIR, $plugin);
            if (yield isfile($absPlugin) && substr($plugin, -4) === '.php') { # Use mb_substr() for unicode file names
                self::$plugins['public'][basename($plugin, '.php')] = require $absPlugin;
                $pluginsCount++;
            } elseif (yield isdir($absPlugin)) {
                self::$plugins[$plugin] = [];
                foreach (yield scandir($absPlugin) as $subPlugin) {
                    $absSubPlugin = makePath($absPlugin, $subPlugin);
                    if (yield isfile($absSubPlugin) && substr($subPlugin, -4) === '.php') { # Use mb_substr() for unicode file names
                        self::$plugins[$plugin][basename($subPlugin, '.php')] = require $absSubPlugin;
                        $pluginsCount++;
                    }
                }
            }
        }
        if ($pluginsCount === 0) {
            $this->logger(new Exception("No plugin found at all"), Logger::ERROR);
            $this->shut();
        }
        $this->logger("All plugins was loaded successfully");
    }

    /**
     * Receives [channel | supergroup]'s updates.
     *
     * @param array $update
     *
     * @return Generator
     */
    public function onUpdateNewChannelMessage(array $update): Generator
    {
        return $this->onUpdateNewMessage($update);
    }

    /**
     * Receives [group | user(& bot)]'s updates.
     *
     * @param array $update
     *
     * @return Generator
     */
    public function onUpdateNewMessage(array $update): Generator
    {
        try {
            if ($update['message']['_'] !== 'message') {
                return;
            }

            $params = [
                'update'   => $update,
                'msg_id'   => $update['message']['id']              ?? null,
                'out'      => $update['message']['out']             ?? false,
                'text'     => $update['message']['message']         ?? null,
                'from_id'  => $update['message']['from_id']         ?? null,
                'reply_id' => $update['message']['reply_to_msg_id'] ?? null];

            switch (true)
            {
                case $params['from_id'] === self::$adminId:
                    $mode = 'admin';
                    break;
                /*
                 * #> Just example <#
                 * case chat_id === xxx:
                 *     $mode = "name of a folder in the plugins directory";
                 *     break;
                 */
                default:
                    $mode = '';
            }
            foreach ((self::$plugins[$mode] ?? []) + self::$plugins['public'] as $name => $plugin) {
                $result = $plugin($params);
                if ($result instanceof Generator) {
                    yield from $result;
                }
            }
        } catch (Throwable $e) {
            $this->logger($e, Logger::ERROR);
            $this->shut();
        }
    }

    /**
     * Converts object to string.
     * Just for development !
     *
     * @param      $object
     * @param bool $code
     *
     * @return string
     */
    public static function objToStr($object, bool $code = false): string
    {
        if ($object instanceof Throwable) {
            $object = (string) $object;
        } elseif (!is_string($object)) {
            try {
                $object = json_encode(
                    $object,
                      JSON_THROW_ON_ERROR
                    + JSON_UNESCAPED_SLASHES
                    + JSON_PRETTY_PRINT
                    + JSON_UNESCAPED_UNICODE
                );
            } catch (JsonException $e) {
                $object = var_export($object, true);
            }
        }
        return $code ? "<code>$object</code>" : $object;
    }

    /**
     * `sendMessage()` & extra features:
     *   - Short name
     *   - Ready params
     *
     * <b>Note: This method doesn't return result of the sendMessage !!!</b>
     *
     * @param array|string|int $peer
     * @param string           $msg
     * @param string|null      $parseMode
     * @param int|null         $replyTo
     * @param array            $extraParams
     * @param array            ...$extra
     *
     * @return Generator
     */
    private function sendMsg($peer, string $msg, ?string $parseMode = null, ?int $replyTo = null,
                             array $extraParams = [], array ...$extra): Generator
    {
        try {
            yield $this->messages->sendMessage(
                ['peer'           => $peer,
                'message'         => $msg,
                'parse_mode'      => $parseMode,
                'reply_to_msg_id' => $replyTo] + $extraParams,
                ...$extra
            );
        } catch (Throwable $e) {
            $this->logger($e, Logger::ERROR);
            $this->shut();
        }
    }

    /**
     * `editMessage()` & extra features:
     *   - Short name
     *   - Ready params
     *
     * <b>Note: This method doesn't return result of the editMessage !!!</b>
     *
     * @param array|string|int $peer
     * @param int              $messageId
     * @param string           $msg
     * @param string|null      $parseMode
     * @param array            $extraParams
     * @param array            ...$extra
     *
     * @return Generator
     */
    private function editMsg($peer, int $messageId, string $msg, ?string $parseMode = null,
                             array $extraParams = [], array ...$extra): Generator
    {
        try {
            yield $this->messages->editMessage(
                ['peer'      => $peer,
                'id'         => $messageId,
                'message'    => $msg,
                'parse_mode' => $parseMode] + $extraParams,
                ...$extra
            );
        } catch (Throwable $e) {
            $this->logger($e, Logger::ERROR);
            $this->shut();
        }
    }

    /**
     * `deleteMessages()` & extra features:
     *   - Short name
     *   - Delayed delete
     *   - Chooses the right method between 'messages.delete...' & 'channels.delete...'
     *
     * <b>Note: This method doesn't return result of the deleteMessages !!!</b>
     *
     * @param array $update
     * @param array $messageIds
     * @param int   $delay
     * @param array ...$extra
     *
     * @return Generator
     */
    private function delMsg(array $update, array $messageIds, int $delay = 0, array ...$extra): Generator
    {
        try {
            if ($delay > 5) {
                $this->callFork((function () use ($update, $messageIds, $delay, $extra): Generator {
                    try {
                        yield $this->sleep($delay);

                        if ($this->getChatType($update) === 'channel') {
                            yield $this->channels->deleteMessages(['channel' => $update, 'id' => $messageIds], ...$extra);
                        } else {
                            yield $this->messages->deleteMessages(['id' => $messageIds, 'revoke' => true], ...$extra);
                        }
                    } catch (Throwable $e) {
                        $this->logger($e, Logger::ERROR);
                        $this->shut();
                    }
                })());
            } else {
                if ($delay > 0) {
                    yield $this->sleep($delay);
                }
                if ($this->getChatType($update) === 'channel') {
                    yield $this->channels->deleteMessages(['channel' => $update, 'id' => $messageIds], ...$extra);
                } else {
                    yield $this->messages->deleteMessages(['id' => $messageIds, 'revoke' => true], ...$extra);
                }
            }
        } catch (Throwable $e) {
            $this->logger($e, Logger::ERROR);
            $this->shut();
        }
    }

    /**
     * Parses the update to find chat type.
     *
     * @param array $update
     *
     * @return string
     * @throws Exception
     */
    public function getChatType(array $update): string
    {
        switch ($update['message']['peer_id']['_'] ?? $update['message']['to_id']['_'] ?? '')
        {
            case 'peerUser':
                return 'user';
            case 'peerChat':
                return 'chat';
            case 'peerChannel':
                return 'channel';
            default:
                throw new Exception("Chat type not found");
        }
    }

    /**
     * Parses the update to find chat id. (bot-API Id style)
     *
     * @param array $update
     *
     * @return int
     * @throws Exception
     */
    private function getChatId(array $update): int
    {
        switch ($update['message']['peer_id']['_'] ?? '')
        {
            case 'peerUser':
                return (int) $update['message']['peer_id']['user_id'];
            case 'peerChat':
                return -1 * (int) $update['message']['peer_id']['chat_id'];
            case 'peerChannel':
                return (int) ('-100' . $update['message']['peer_id']['channel_id']);
            default:
                throw new Exception("Chat Id not found");
        }
    }

    /**
     * Completely turns off the robot.
     *
     * @param int $code
     */
    public function shut(int $code = 0): void
    {
        if (!self::$isCli) {
            Shutdown::removeCallback('restarter');
        }
        $this->stop();
        Magic::shutdown($code); // Just to ensure that the robot shuts down
    }
}
