<?php

namespace Niekert\DiscordMCPE;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Niekert\DiscordMCPE\Events\EventListener;

class Main extends PluginBase implements Listener
{

    public $webhook, $username, $startupopt, $shutdownopt, $joinopt, $quitopt, $deathopt, $debugopt, $commandopt, $chaturl, $chatformat, $chatprefix, $chatopt, $chatuser, $pp;

    private $configversion = '1.0.2';

    public function onEnable()
    {
        $this->setvars();
        if ($this->isDisabled()) {
            return;
        }
        if ($this->pp) {
            if (is_null(this->getServer()->getPluginManager()->getPlugin('PurePerms'))) {
                $this->getLogger()->info('PurePerms Compatibility Enabled!');
            } else {
                $this->getLogger()->alert('PurePerms Compatibility Enabled, but PurePerms could not be found!');
                $this->getLogger()->alert('Edit your settings in config.yml!');
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return false;
            }
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        if ($this->getConfig()->get('start_message') !== '0') {
            $this->sendMessage($this->webhook, $this->startupopt, 'CONSOLE');
        }
    }

    public function onDisable()
    {
        if ($this->shutdownopt !== '0' AND !$this->isEnabled()) {
            $this->sendMessage($this->webhook, $this->shutdownopt);
        }
    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if ($cmd->getName() == 'discord') {
            if ($this->commandopt) {
                $ppa = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
                if (!isset($args[0])) {
                    $sender->sendMessage(C::RED . 'Please provide an argument! Usage: /discord (message).');
                } elseif ($this->pp) {
                    if ($sender instanceof Player) {
                        $format = str_replace(['{rank}', '{player}', '{message}'], [C::clean($ppa->getUserDataMgr()->getGroup($sender)), $sender->getName(), implode(' ', $args)], $this->chatformat);
                        $this->sendMessage($this->chaturl, $format, $sender->getName(), $this->chatuser);
                    } else {
                        $this->getLogger()->notice('You cannot execute this command as console!');
                    }
                } else {
                    $format = str_replace(['{player}', '{message}'], [C::clean($sender->getName()), implode(' ', $args)], $this->chatformat);
                    $this->sendMessage($this->chaturl, $format, $sender->getName(), $this->chatuser);
                }
            } else {
                $sender->sendMessage(C::RED . 'Sorry, but the owner disabled this option.');
            }
        }
        return true;
    }

    private function setvars()
    {
        $this->saveDefaultConfig();
        if (key_exists('Version', $this->getConfig()->getAll())) {
            if ($this->configversion !== $this->getConfig()->get('Version')) {
                $this->getLogger()->critical('Please update your config!');
                $this->setEnabled(false);
                return;
            }
        } else {
            $this->getLogger()->critical('Please update your config!');
            $this->setEnabled(false);
            return;
        }
        foreach ($this->getConfig()->getAll() as $item) {
            if (!isset($item) OR $item === '') {
                $this->getLogger()->info('Please edit your config!');
                $this->setEnabled(false);
                return;
            }
        }
        $this->reloadConfig();
        $this->webhook = $this->getConfig()->get('webhook_url');
        $this->username = $this->getConfig()->get('username');
        $this->startupopt = $this->getConfig()->get('start_message');
        $this->shutdownopt = $this->getConfig()->get('shutdown_message');
        $this->joinopt = $this->getConfig()->get('join_message');
        $this->quitopt = $this->getConfig()->get('quit_message');
        $this->deathopt = $this->getConfig()->get('death_message');
        $this->debugopt = $this->getConfig()->get('debug');
        $this->commandopt = $this->getConfig()->get('command');
        $this->chatformat = $this->getConfig()->get('chat_format');
        $this->chatprefix = $this->getConfig()->get('chat_prefix');
        $this->chatopt = $this->getConfig()->get('chat');
        $this->pp = $this->getConfig()->get('pureperms');

        //Some statements
        if ($this->getConfig()->get('chat_username') === '0') {
            $this->chatuser = $this->username;
        } elseif ($this->getConfig()->get('chat_username') !== '0') {
            $this->chatuser = $this->getConfig()->get('chat_username');
        }
        if ($this->getConfig()->get('chat_url') === '0') {
            $this->chaturl = $this->webhook;
        } elseif ($this->getConfig()->get('chat_url') !== '0') {
            $this->chaturl = $this->getConfig()->get('chat_url');
        }
    }

    /**
     * @param $player
     * @param $result
     */
    public function notify($player, $result)
    {
        if ($player === 'nolog') {
            return;
        } elseif ($player === 'CONSOLE') {
            $player = new ConsoleCommandSender();
        } else {
            $playerinstance = $this->getServer()->getPlayerExact($player);
            if ($playerinstance === null) {
                return;
            } else {
                $player = $playerinstance;
            }
        }
        if ($result['success']) {
            $player->sendMessage(C::AQUA . '[Discord-MCPE] ' . C::GREEN . 'Discord message was sent!');
        } else {
            if ($this->getConfig()->get('debug')) {
                $this->getLogger()->error(C::RED . 'Error: ' . $result['Error']);
            } else {
                $this->getLogger()->warning(C::RED . 'Something strange happened. Set debug in config to true to get error message');
            }
            $player->sendMessage(C::AQUA . '[Discord-MCPE] ' . C::GREEN . 'Discord message wasn\'t sent!');
        }
    }

    /**
     * @param $webhook
     * @param $message
     * @param string $player
     * @param null $username
     */
    public function sendMessage($webhook, $message, string $player = 'nolog', $username = null)
    {
        if (!isset($username)) {
            $username = $this->username;
        }
        $curlopts = [
            'content' => $message,
            'username' => $username
        ];
        $this->getServer()->getAsyncPool()->submitTask(new Tasks\SendTaskAsync($player, $webhook, serialize($curlopts)));
    }
}
