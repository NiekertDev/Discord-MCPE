<?php

namespace Niekert\DiscordMCPE;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener{

	public function onLoad(){
		$this->getLogger()->info("Plugin loading");
	}
		
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->saveDefaultConfig();
		$this->getLogger()->info("Plugin enabled");
		$this->reloadConfig();
		$this->webhook = $this->getConfig()->get("webhook_url");
		$this->botusername = $this->getConfig()->get("username");
		$this->startupopt = $this->getConfig()->get("start_message");
		$this->shutdownopt = $this->getConfig()->get("shutdown_message");
		$this->joinopt = $this->getConfig()->get("join_message");
		$this->quitopt = $this->getConfig()->get("quit_message");
		$this->deathopt = $this->getConfig()->get("death_message");
		$this->debugopt = $this->getConfig()->get("debug");

			//I'm to lazy to set a message for all options :)
				
				if($this->webhook === "" OR $this->botusername === "" OR $this->startupopt === "" OR $this->shutdownopt === "" OR $this->joinopt === "" OR $this->quitopt === "" OR $this->deathopt === ""){
					$this->getLogger()->warning('Please edit your config.yml');
					$this->setEnabled(false);
					return;
				}
                
				elseif($this->startupopt !== "0"){
					$this->send($this->startupopt, $this->botusername);
						if($this->error === "0"){
							$this->getLogger()->info('Check your Discord Server now :)');
						}
				}
	}
	
	public function onDisable(){
        $this->getLogger()->info("Plugin Disabled");
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

	function send($message, $username){
		$data = array("content" => $message, "username" => "$username");
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->webhook);
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($curl);
			$curlerror = curl_error($curl);
			
				if($response === false AND $this->debugopt === "1"){
					$this->getLogger()->warning('ERROR: ' .$curlerror);
					$error = "1";
				}
				
				elseif($response === false AND $this->debugopt === "0"){
					$this->getLogger()->warning('Something strange happened :(. Set the debug option in the config to 1 to show the error.');
					$error = "1";
				}
				
				elseif($response === ""){
					$error = "0";
				}
			$this->error = $error;
	}
}