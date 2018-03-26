<?php

namespace Niekert\DiscordMCPE;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Niekert\DiscordMCPE\Events\EventListener;

class Main extends PluginBase implements Listener
{

    public $webhook, $username, $startupopt, $shutdownopt, $joinopt, $quitopt, $deathopt, $debugopt, $commandopt, $chaturl, $chatformat, $chatprefix, $chatopt, $chatuser, $pp;

    private $configversion = "1.0.2";

    public function onLoad()
    {
        $this->getLogger()->info("Plugin loading");
    }

    public function onEnable()
    {
        $this->setvars();
        if ($this->isDisabled()) {
            return;
        }
        if ($this->pp == true) {
            if ($this->getServer()->getPluginManager()->getPlugin('PurePerms') == true) {
                $this->getServer()->getLogger()->info(C::GREEN . 'PurePerms Compatibility Enabled!');
            } else {
                $this->getServer()->getLogger()->info(C::RED . 'PurePerms Compatibility Enabled, but PurePerms could not be found!');
            }
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info(C::GREEN . "Plugin enabled");
        if ($this->getConfig()->get("start_message") !== "0") {
            $this->sendMessage($this->webhook, $this->startupopt, "CONSOLE");
        }
    }

    public function onDisable()
    {
        $this->getLogger()->info(C::RED . "Plugin Disabled");
        if ($this->shutdownopt !== "0" AND !$this->isEnabled()) {
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
        if ($cmd->getName() == "discord") {
            if ($this->commandopt) {
                if (!isset($args[0])) {
                    $sender->sendMessage(C::RED . "Please provide an argument! Usage: /discord (message).");
                } else {
                    $format = str_replace(['{player}', '{message}'], [$sender->getName(), implode(" ", $args)], $this->chatformat);
                    $this->sendMessage($this->chaturl, $format, $sender->getName(), $this->chatuser);
                }
            } else {
                $sender->sendMessage(C::RED . "Sorry, but the owner disabled this option.");
            }
        }
        return true;
    }

    private function setvars()
    {
        $this->saveDefaultConfig();
        if (key_exists("Version", $this->getConfig()->getAll())) {
            if ($this->configversion !== $this->getConfig()->get("Version")) {
                $this->getLogger()->critical("Please update your config!");
                $this->setEnabled(false);
                return;
            }
        } else {
            $this->getLogger()->critical("Please update your config!");
            $this->setEnabled(false);
            return;
        }
        foreach ($this->getConfig()->getAll() as $item) {
            if (!isset($item) OR $item === "") {
                $this->getLogger()->info("Please edit your config");
                $this->setEnabled(false);
                return;
            }
        }
        $this->reloadConfig();
        $this->webhook = $this->getConfig()->get("webhook_url");
        $this->username = $this->getConfig()->get("username");
        $this->startupopt = $this->getConfig()->get("start_message");
        $this->shutdownopt = $this->getConfig()->get("shutdown_message");
        $this->joinopt = $this->getConfig()->get("join_message");
        $this->quitopt = $this->getConfig()->get("quit_message");
        $this->deathopt = $this->getConfig()->get("death_message");
        $this->debugopt = $this->getConfig()->get("debug");
        $this->commandopt = $this->getConfig()->get("command");
        $this->chatformat = $this->getConfig()->get("chat_format");
        $this->chatprefix = $this->getConfig()->get("chat_prefix");
        $this->chatopt = $this->getConfig()->get("chat");
        $this->pp = $this->getConfig()->get("pureperms");

        //Some statements
        if ($this->getConfig()->get("chat_username") === "0") {
            $this->chatuser = $this->username;
        } elseif ($this->getConfig()->get("chat_username") !== "0") {
            $this->chatuser = $this->getConfig()->get("chat_username");
        }
        if ($this->getConfig()->get("chat_url") === "0") {
            $this->chaturl = $this->webhook;
        } elseif ($this->getConfig()->get("chat_url") !== "0") {
            $this->chaturl = $this->getConfig()->get("chat_url");
        }
    }

    public function notify($player, $result)
    {
        if ($player === "nolog") {
            return;
        } elseif ($player === "CONSOLE") {
            $player = new ConsoleCommandSender();
        } else {
            $playerinstance = $this->getServer()->getPlayerExact($player);
            if ($playerinstance === null) {
                return;
            } else {
                $player = $playerinstance;
            }
        }
        if ($result["success"]) {
            $player->sendMessage(C::AQUA . "[Discord-MCPE] " . C::GREEN . "Discord message was send!");
        } else {
            if ($this->getConfig()->get("debug")) {
                $this->getLogger()->error(C::RED . "Error: " . $result["Error"]);
            } else {
                $this->getLogger()->warning(C::RED . "Something strange happened. Set debug in config to true to get error message");
            }
            $player->sendMessage(C::AQUA . "[Discord-MCPE] " . C::GREEN . "Discord message wasn't send!");
        }

    }

    /**
     * @param $webhook
     * @param $message
     * @param string $player
     * @param null $username
     */
    public function sendMessage($webhook, $message, string $player = "nolog", $username = null)
    {
        if (!isset($username)) {
            $username = $this->username;
        }
        $curlopts = [
            "content" => $message,
            "username" => $username
        ];
        $this->getServer()->getScheduler()->scheduleAsyncTask(new Tasks\SendTaskAsync($player, $webhook, serialize($curlopts)));
    }
}
