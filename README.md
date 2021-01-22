# Plugin-Based Robot

**Async and plugin-based [MadelineProto](https://github.com/danog/MadelineProto) (user-)bot template**

<br>

## Requirements

- PHP 7.4
- MadelineProto [requirements](https://docs.madelineproto.xyz/docs/REQUIREMENTS.html)

<br>

## Installation

#### 1. Clone the repository

```bash
$ git clone https://github.com/Piagrammist/plugin-sys && cd plugin-sys/
```

#### 2. Put your information in [configuration class](Config.php)

> Note that you can skip this step and bot works fine !

#### 3. Run the robot

```bash
$ php client.php
```

#### 4. Add a plugin & enjoy

> **Note**\
> You don't need to `restart` the robot when you added (removed, etc...) a new plugin.\
> Just use `reload` command to refresh the plugins list.

<br>

## How does it work ?

#### Private plugins

You can create several directories in the [`Plugins` folder](plugins) to specify different groups of plugins, which have different jobs.

> For example, I 've created the [`Admin`](plugins/admin) section.

Each group can have several PHP files that each of them returns a closure.

#### Public plugins

Just put your plugins in [`Plugins` folder](plugins) and they will be known as public plugins.\
It means they will be executed when anyone sends a message.

<br>

## Example Plugin

|                  Name                  |        Command         | Description                                                                         |
| :------------------------------------: | :--------------------: | :-----------------------------------------------------------------------------------|
| [_Restart_](plugins/admin/restart.php) |    `restart`, `re`     | Restarts the robot. (just works on web-server)                                      |
|    [_Stop_](plugins/admin/stop.php)    |     `stop`, `die`      | Completely stops the robot. (also works on web-server)                              |
|  [_Reload_](plugins/admin/reload.php)  |        `reload`        | Reloads plugins list.                                                               |
|    [_Dump_](plugins/admin/dump.php)    |          `{}`          | Dumps and sends `message` field of the `update` of that message. (Supports replies) |
|    [_Ping_](plugins/admin/ping.php)    |         `ping`         | A simple ping-pong command to check whether the robot is online.                    |
|   [_Stats_](plugins/admin/stats.php)   |   `status`, `stats`    | Sends some statistics from the robot (user) & server.                               |
|  [_Delete_](plugins/admin/delete.php)  |  `delete X`, `del X`   | Deletes the message after **X** seconds. (Supports replies)                         |
|      [_Start_](plugins/start.php)      | `/start`, `/start arg` | Start command for bot-API.                                                          |

> Most of the commands works with a prefix (anything except the alphabet & numbers).

## Contact
If you have any question, contact me via [Telegram](https://t.me/Piagrammist).

