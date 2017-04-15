<?php

namespace Niekert\DiscordMCPE;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener{

	public function onLoad(){
		$this->getLogger()->info("Plugin loading");
	}
		
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->saveDefaultConfig();
		$this->getLogger()->info(TextFormat::GREEN."Plugin enabled");
		$this->setvars();
		
				if($this->startupopt !== "0" AND $this->webhook !== "" AND $this->botusername !== "" AND $this->startupopt !== ""){
					$this->send($this->startupopt, $this->botusername);
						if($this->error === "0"){
							$this->getLogger()->info(TextFormat::GREEN.'Check your Discord Server now :)');
						}
				}
	}
	
	public function onDisable(){
        $this->getLogger()->info(TextFormat::RED."Plugin Disabled");
		if($this->shutdownopt !== "0" AND $this->webhook !== "" AND $this->botusername !== "" AND $this->startupopt !== ""){
			$this->send($this->shutdownopt, $this->botusername);
		}
    }

	public function onJoin(PlayerJoinEvent $event){
		$temp1 = $event->getPlayer();
		$player = $temp1->getName();
		if($this->joinopt !== "0"){
			$this->send(str_replace("{player}","$player","$this->joinopt"), $this->botusername);
		}
	}

	public function onQuit(PlayerQuitEvent $event){
		$temp2 = $event->getPlayer();
		$player = $temp2->getName();
		if($this->quitopt !== "0"){
			$this->send(str_replace("{player}","$player","$this->quitopt"), $this->botusername);
		}
	}	

	public function onDeath(PlayerDeathEvent $event){
		$temp3 = $event->getEntity();
		$player = $temp3->getName();
		if($this->joinopt !== "0"){
			$this->send(str_replace("{player}","$player","$this->deathopt"), $this->botusername);
		}
	}
	
	public function onChat(PlayerChatEvent $event){
		$message = $event->getMessage();
		$sender = $event->getPlayer();
		if($this->chatprefix !== "0"){
			$format = str_replace(array('{player}', '{message}'), array($sender->getName(), ltrim($message, $this->chatprefix)), $this->chatformat);
			if(substr($message, 0, 1 ) === $this->chatprefix){
				$event->setCancelled(true);
				$this->send($format, $this->chatuser, $this->chaturl);
				if($this->error === "0"){
					$sender->sendMessage(TextFormat::GREEN."Discord message was send.");
				}
				elseif($this->error === "1"){
					$sender->sendMessage(TextFormat::RED."Discord message wasn't send.");
				}
			}
		}
		if($this->chatopt !== "0"){
			$format = str_replace(array('{player}', '{message}'), array($sender->getName(), $message), $this->chatformat);
			$this->send($format, $this->chatuser, $this->chaturl);
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if($cmd->getName() == "discord"){
			if($this->commandopt !== "0"){
				if(!isset($args[0])) {
					$sender->sendMessage(TextFormat::RED."Please provide an argument! Usage: /discord (message).");
				}
				else{
					$format = str_replace(array('{player}', '{message}'), array($sender->getName(), implode(" ", $args)), $this->chatformat);
					$this->send($format, $this->chatuser, $this->chaturl);
					if($this->error === "0"){
						$sender->sendMessage(TextFormat::GREEN."Discord message was sent.");
					}
					elseif($this->error === "1"){
						$sender->sendMessage(TextFormat::RED."Discord message wasn't sent.");
					}
				}
			}
			else{
				$sender->sendMessage(TextFormat::RED."Sorry, but the owner disabled this option.");
			}
		}
	return true;
	}
	
	function setvars(){
		$this->reloadConfig();
		$this->webhook = $this->getConfig()->get("webhook_url");
		$this->botusername = $this->getConfig()->get("username");
		$this->startupopt = $this->getConfig()->get("start_message");
		$this->shutdownopt = $this->getConfig()->get("shutdown_message");
		$this->joinopt = $this->getConfig()->get("join_message");
		$this->quitopt = $this->getConfig()->get("quit_message");
		$this->deathopt = $this->getConfig()->get("death_message");
		$this->debugopt = $this->getConfig()->get("debug");
		$this->commandopt = $this->getConfig()->get("command");
		$this->chaturlopt = $this->getConfig()->get("chat_url");
		$this->chatformat = $this->getConfig()->get("chat_format");
		$this->chatprefix = $this->getConfig()->get("chat_prefix");
		$this->chatopt = $this->getConfig()->get("chat");
		
			//Some statements
			//I'm to lazy to set a message for all options :)
			if($this->webhook === "" OR $this->botusername === "" OR $this->startupopt === "" OR $this->shutdownopt === "" OR $this->joinopt === "" OR $this->quitopt === "" OR $this->deathopt === "" OR $this->getConfig()->get("chat_url") === "" OR $this->getConfig()->get("chat_username") === ""){
				$this->getLogger()->warning(TextFormat::RED.'Please edit your config.yml');
				$this->setEnabled(false);
				return;
			}
			if($this->getConfig()->get("chat_username") === "0"){
				$this->chatuser = $this->botusername;
			}
			elseif($this->getConfig()->get("chat_username") !== "0"){
				$this->chatuser = $this->getConfig()->get("chat_username");
			}
			if($this->getConfig()->get("chat_url") === "0"){
				$this->chaturl = $this->webhook;
			}
			elseif($this->getConfig()->get("chat_url") !== "0"){
				$this->chaturl = $this->getConfig()->get("chat_url");
			}
	}

	public function error($error){
		if($error !== "0"){
			$this->getLogger()->error(TextFormat::RED.$error);
			$this->error = "1";
		}
		else{
			$this->error = "0";
		}
	}
	
	function send($message, $username, $webhook = ""){
		if($webhook === ""){
			$webhook = $this->webhook;
		}
		$task = new SendMessage($this, $message, $username, $webhook);
		$this->getServer()->getScheduler()->scheduleAsyncTask($task);
	}
}
