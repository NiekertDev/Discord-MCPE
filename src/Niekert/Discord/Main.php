<?php

namespace Niekert\Discord;

use pocketmine\plugin\PluginBase;
use pocketmine/utils/Utils;

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
		$this->reloadConfig();
		$data = array("content" => $message, "username" => "$username",);
		$curl = curl_init("$webhook");
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		return curl_exec($curl);
	}
}
