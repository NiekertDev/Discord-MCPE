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
	
	function send($message, $username){
		$debug = $this->getConfig()->get("debug");
		$webhook = $this->getConfig()->get("webhook_url");
		$data = array("content" => $message, "username" => "$username");
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $webhook);
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_exec($curl);
			$response = curl_exec($curl);
			curl_close($curl);
						
				if(!is_null($response)){
					$error = 0;
				}
				
				elseif($debug == 1 AND !is_null($response)){
					$this->getLogger()->warning('ERROR: $response');
					$error = 1;
				}
				
				elseif($debug == 0 AND !is_null($response)){
					$this->getLogger()->warning('Something strange happened...');
					$error = 1;
				}
				
				else {
					$error = 1;
				}
	}
					
	public function onLoad(){
		$this->getLogger()->info("Plugin loading");
	}
		
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->saveDefaultConfig();
		$this->getLogger()->info("Plugin enabled");
		$this->reloadConfig();
		$webhook = $this->getConfig()->get("webhook_url");
		$username = $this->getConfig()->get("username");
		$startupopt = $this->getConfig()->get("start_message");
                
                // Statements
                
                if ($username === "") {
                    $this->getLogger()->warning('Please set your username in config.yml');
                }
                elseif ($webhook === "") {
                    $this->getLogger()->warning('Please set your webhook in config.yml');
                }
				elseif ($startupopt === "0") {
				$event->setCancelled();
				}
				elseif ($startupopt === "") {
                    $this->getLogger()->warning('Please set your message in config.yml');
                }
                else {
					$this->send("$startupopt", "$username");
					if ($error === 0) {
						$this->getLogger()->warning('Check your Discord Server now :)');
					}
				}
	}
	
	public function onDisable(){
        $this->getLogger()->info("Plugin Disabled");
		$shutdownopt = $this->getConfig()->get("shutdown_message");
		if ($shutdownopt === "0") {
			$event->setCancelled();
		}
		else {
			$this->send("$shutdownopt", "$username");
		}
    }

	public function onJoin(PlayerJoinEvent $event){
		$playerjoinopt = $this->getConfig()->get("join_message");
		if ($playerjoinopt === "0") {
			$event->setCancelled();
		}
		
		else {
			$temp = $event->getPlayer();
			$player = $temp->getName();
			$this->send("$playerjoinopt", "$username");
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
		$playerquitopt = $this->getConfig()->get("quit_message");
		if ($playerquitopt === "0") {
			$event->setCancelled();
		}
		
		else {
			$temp = $event->getPlayer();
			$player = $temp->getName();
			$this->send("$playerquitopt", "$username");
		}
	}	
	
	public function onDeath(PlayerDeathEvent $event){
		$playerddeathopt = $this->getConfig()->get("death_message");
		if ($playerdeathopt === "0") {
			$event->setCancelled();
		}
		
		else {
			$temp = $event->getPlayer();
			$player = $temp->getName();
			$this->send("$playerdeathopt", "$username");
		}
	}	
}