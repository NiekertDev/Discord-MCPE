<?php

namespace Niekert\DiscordMCPE\Events;

use Niekert\DiscordMCPE\Main;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerDeathEvent, PlayerChatEvent};;

class EventListener implements Listener
{
    private $main, $config, $webhook, $ppa;

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->main = $plugin;
        $this->config = new Config($this->main->getDataFolder() . 'config.yml', Config::YAML, array());
        $this->webhook = $this->config->get('webhook_url');
        $this->ppa = $this->main->getServer()->getPluginManager()->getPlugin('PurePerms');
    }


    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event)
    {
        $playername = $event->getPlayer()->getDisplayName();
        if ($this->main->joinopt !== '0') {
            if ($this->main->pp !== null) {
                $this->main->sendMessage($this->webhook, str_replace('{rank}', C::clean($this->ppa->getUserDataMgr()->getGroup($event->getPlayer())), str_replace('{player}', C::clean($playername), $this->main->joinopt)));
            } else {
                $this->main->sendMessage($this->webhook, str_replace('{player}', C::clean($playername), $this->main->joinopt));
            }
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event)
    {
        $playername = $event->getPlayer()->getDisplayName();
        if ($this->main->quitopt !== '0') {
            if ($this->main->pp !== null) {
                $this->main->sendMessage($this->webhook, str_replace('{rank}', C::clean($this->ppa->getUserDataMgr()->getGroup($event->getPlayer())), str_replace('{player}', C::clean($playername), $this->main->quitopt)));
            } else {
                $this->main->sendMessage($this->webhook, str_replace('{player}', C::clean($playername), $this->main->quitopt));
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event)
    {
        $playername = $event->getEntity()->getDisplayName();
        if ($this->main->deathopt !== '0') {
            if ($this->main->pp !== null) {
                $this->main->sendMessage($this->webhook, str_replace('{rank}', C::clean($this->ppa->getUserDataMgr()->getGroup($event->getPlayer())), str_replace('{player}', C::clean($playername), $this->main->deathopt)));
            } else {
                $this->main->sendMessage($this->webhook, str_replace('{player}', C::clean($playername), $this->main->deathopt));
            }
        }
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event)
    {
        if ($event->isCancelled()) return;
        $message = $event->getMessage();
        $sender = $event->getPlayer();
        $chaturl = $this->main->chaturl;
        if ($this->main->chatprefix !== '0') {
            if ($this->main->pp !== null) {
                $format = str_replace(['{rank}', '{player}', '{message}'], [C::clean($this->ppa->getUserDataMgr()->getGroup($event->getPlayer())), C::clean($sender->getName()), ltrim($message, $this->main->chatprefix)], $this->main->chatformat);
                if (substr($message, 0, 1) === $this->main->chatprefix) {
                    $event->setCancelled();
                    $this->main->sendMessage($chaturl, $format, $sender->getName(), $this->main->chatuser);
                }
            } else {
                $format = str_replace(['{player}', '{message}'], [C::clean($sender->getName()), ltrim($message, $this->main->chatprefix)], $this->main->chatformat);
                if (substr($message, 0, 1) === $this->main->chatprefix) {
                    $event->setCancelled();
                    $this->main->sendMessage($chaturl, $format, $sender->getName(), $this->main->chatuser);
                }
            }
            if ($this->main->chatopt) {
                if ($this->main->pp !== null) {
                    $format = str_replace(array('{rank}', '{player}', '{message}'), array(C::clean($this->ppa->getUserDataMgr()->getGroup($event->getPlayer())), C::clean($sender->getName()), $message), $this->main->chatformat);
                    $this->main->sendMessage($chaturl, $format, 'nolog', $this->main->chatuser);
                } else {
                    $format = str_replace(array('{player}', '{message}'), array(C::clean($sender->getName()), $message), $this->main->chatformat);
                    $this->main->sendMessage($chaturl, $format, 'nolog', $this->main->chatuser);
                }
            }
        }
    }
}