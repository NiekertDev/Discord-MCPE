<?php

namespace Niekert\DiscordMCPE\Events;

use Niekert\DiscordMCPE\Main;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\{PlayerJoinEvent,PlayerQuitEvent, PlayerDeathEvent, PlayerChatEvent};

class EventListener implements Listener
{
    private $main,$config,$webhook;
    public function __construct(Main $plugin)
    {
        $this->main = $plugin;
        $this->config = new Config($this->main->getDataFolder(). "config.yml", Config::YAML, array());
        $this->webhook = $this->config->get("webhook_url");
    }

    public function onJoin(PlayerJoinEvent $event){
        if($event->isCancelled()) return;
        $playername = $event->getPlayer()->getNameTag();
        if($this->main->joinopt !== "0"){
            $this->main->sendMessage($this->webhook, str_replace("{player}","$playername","$this->main->joinopt"));
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        if($event->isCancelled()) return;
        $playername = $event->getPlayer()->getNameTag();
        if($this->main->quitopt !== "0"){
            $this->main->sendMessage($this->webhook, str_replace("{player}","$playername","$this->main->quitopt"));
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        if($event->isCancelled()) return;
        $playername = $event->getEntity()->getNameTag();
        if($this->main->deathopt !== "0"){
            $this->main->sendMessage($this->webhook, str_replace("{player}","$playername","$this->main->deathopt"));
        }
    }

    public function onChat(PlayerChatEvent $event){
        if($event->isCancelled()) return;
        $message = $event->getMessage();
        $sender = $event->getPlayer();
        if($this->main->chatprefix !== "0"){
            $format = str_replace(["{player}", "{message}"], [$sender->getName(), ltrim($message, $this->main->chatprefix)], $this->main->chatformat);
            if(substr($message, 0, 1 ) === $this->main->chatprefix){
                $event->setCancelled(true);
                $this->main->sendMessage($this->main->chaturl, $format, $sender, $this->main->chatuser);
            }
        }
        if($this->main->chatopt){
            $format = str_replace(array('{player}', '{message}'), array($sender->getName(), $message), $this->main->chatformat);
            $this->main->sendMessage($this->main->chaturl, $format, "console", $this->main->chatuser);
        }
    }
}