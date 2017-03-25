<?php

namespace Niekert\Discord;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Utils;
use pocketmine\utils\Config;

class Main extends PluginBase{
	
	public function onLoad(){
		$this->getLogger()->info("Plugin loading");
	}
		
	public function onEnable(){
		$this->getLogger()->info("Plugin enabled");
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$username = $this->getConfig()->get("username");
		$message = $this->getConfig()->get("message");
		$webhook = $this->getConfig()->get("webhook_url");
                
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
                    $curl = curl_init($webhook);
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($curl);
                    
                    // Let you know here!
                    $this->getLogger()->warning('Check your Discord Server now :)');
                
                }
	}
}