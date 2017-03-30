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
	
	public $error;
	
	function send($message, $username){
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
				if(curl_exec($curl) === false){
					$this->getLogger()->warning('Error: curl_error($curl)');
				}
				else{
					$error = 0;
				}
			curl_close($curl);
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
		$botusername = $this->getConfig()->get("username");
		$startupopt = $this->getConfig()->get("start_message");
		$shutdownopt = $this->getConfig()->get("shutdown_message");
		$joinopt = $this->getConfig()->get("join_message");
		$quitopt = $this->getConfig()->get("quit_message");
		$deathopt = $this->getConfig()->get("death_message");
		$error = 1;
		
			//I'm to lazy to set a message for all options :)
				
				if($webhook === "" OR $botusername === "" OR $startupopt === "" OR $shutdownopt === "" OR $joinopt === "" OR $quitopt === "" OR $deathopt === ""){
					$this->getLogger()->warning('Please edit your config.yml');
					$this->setEnabled(false);
				}
                
                else {
					$this->send($startupopt, $botusername);
					if($error === 0){
						$this->getLogger()->info('Check your Discord Server now :)');
					}
					
					else{
					}
				}
	}
	
	public function onDisable(){
        $this->getLogger()->info("Plugin Disabled");
		$shutdownopt = $this->getConfig()->get("shutdown_message");
		$botusername = $this->getConfig()->get("username");
		if ($shutdownopt === "0") {
			$event->setCancelled();
		}
		else {
			$this->send("$shutdownopt", "$botusername");
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
			$this->send("$playerjoinopt", "$botusername");
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
			$this->send("$playerquitopt", "$botusername");
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
			$this->send("$playerdeathopt", "$botusername");
		}
	}	
}