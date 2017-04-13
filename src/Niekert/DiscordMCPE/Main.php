<?php

namespace Niekert\DiscordMCPE;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
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
		
				if($this->startupopt !== "0"){
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
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if($cmd->getName() == "discord"){
			if($this->commandopt !== "0"){
				if(!isset($args[0])) {
					$sender->sendMessage(TextFormat::RED."Please provide an argument! Usage: /discord (message).");
				}
				else{
					$format = str_replace(array('{player}', '{message}'), array($sender->getName(), implode(" ", $args)), $this->commandformatopt);
					$this->send($format, $this->chatuser, $this->chaturl);
					if($this->error === "0"){
						$sender->sendMessage(TextFormat::GREEN."Discord message was send.");
					}
					elseif($this->error === "1"){
						$sender->sendMessage(TextFormat::RED."Discord message wasn't send.");
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
		$this->commandformatopt = $this->getConfig()->get("command_format");
		
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

	function send($message, $username, $webhook = ""){
		if($webhook === ""){
			$webhook = $this->webhook;
		}
		$data = array("content" => $message, "username" => "$username");
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $webhook);
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($curl);
			$curlerror = curl_error($curl);
			
				if($response === false AND $this->debugopt === "1"){
					$this->getLogger()->error(TextFormat::RED.'ERROR: ' .$curlerror);
					$error = "1";
				}
				
				elseif($response === false AND $this->debugopt === "0"){
					$this->getLogger()->warning(TextFormat::RED.'Something strange happened :(. Set the debug option in the config to 1 to show the error.');
					$error = "1";
				}
				
				elseif($response === ""){
					$error = "0";
				}
			$this->error = $error;
	}
}
