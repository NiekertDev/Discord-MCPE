<?php

namespace Niekert\DiscordMCPE;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener{
	
	public function onLoad(){
		$this->getLogger()->info("Plugin loading");
	}
		
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info("Plugin enabled");
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$username = $this->getConfig()->get("username");
		$message = $this->getConfig()->get("message");
		$webhook = $this->getConfig()->get("webhook_url");
		$playerjoinopt = $this->getConfig()->get("join_message");
                
                // Statements
                
                if ($username === "") {
                    $this->getLogger()->warning('Please set your username in config.yml');
                }
                elseif ($webhook === "") {
                    $this->getLogger()->warning('Please set your webhook in config.yml');
                }
                elseif ($message === "") {
                    $this->getLogger()->warning('Please set your message in config.yml');
                }
                else {
                    $data = array("content" => $message, "username" => "$username");
                    $curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $webhook);
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
					curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_exec($curl);
                    
                    // Let you know here!
                    $this->getLogger()->warning('Check your Discord Server now :)');
                
                }
	}

	public function onJoin(PlayerJoinEvent $event){
		if ($playerjoinopt === "0") {
			$event->setCancelled()
  	}
		
		else {
			$name = $event->getPlayer();
			$player = $player->getName();
			$playerjoindata = array("content" => $playerjoinopt, "username" => "$username");
  	            $curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $webhook);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($playerjoindata));
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_exec($curl);
		}
	}
}