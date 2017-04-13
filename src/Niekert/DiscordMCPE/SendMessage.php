<?php

namespace Niekert\DiscordMCPE;

use pocketmine\scheduler\AsyncTask;
use Niekert\DiscordMCPE\Main;

class SendMessage extends AsyncTask {

	public function __construct($main, $message, $username, $webhook = "") {
		$this->main = $main;
		$this->message = $message;
		$this->username = $username;
		$this->webhook = $webhook;
	}
	
	public function onRun() {
		if($this->webhook === ""){
			$webhook = $this->main->webhook;
		}
		$main = $this->main;
		$data = array("content" => $this->message, "username" => "$this->username");
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
			$main->error($error);
	}
}