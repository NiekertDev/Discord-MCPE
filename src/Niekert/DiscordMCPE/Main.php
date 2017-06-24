<?php

namespace Niekert\DiscordMCPE;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Niekert\DiscordMCPE\Events\EventListener;

class Main extends PluginBase implements Listener{
	
	public $webhook, $username, $startupopt, $shutdownopt, $joinopt, $quitopt, $deathopt, $debugopt, $commandopt, $chaturl, $chatformat, $chatprefix, $chatopt, $chatuser;

	private $configversion = "1.0.0";

	public function onLoad(){
		$this->getLogger()->info("Plugin loading");
	}
		
	public function onEnable(){
        $this->setvars();
        if(!$this->isEnabled()) return;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getLogger()->info(C::GREEN."Plugin enabled");
        if($this->getConfig()->get("start_message") !== "0"){
            $player = new ConsoleCommandSender();
            $this->sendMessage($this->webhook, $this->startupopt, $player);
        }
	}
	
	public function onDisable(){
        $this->getLogger()->info(C::RED."Plugin Disabled");
		if($this->shutdownopt !== "0" AND !$this->isEnabled()){
			$this->sendMessage($this->webhook, $this->shutdownopt);
		}
    }

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if($cmd->getName() == "discord"){
			if($this->commandopt){
				if(!isset($args[0])) {
					$sender->sendMessage(C::RED."Please provide an argument! Usage: /discord (message).");
				}
				else{
					$format = str_replace(['{player}', '{message}'], [$sender->getName(), implode(" ", $args)], $this->chatformat);
					$response = $this->sendMessage($this->chaturl, $format, $sender, $this->chatuser);
					if($response){
						$sender->sendMessage(C::GREEN."Discord message was sent.");
					}
					elseif(!$response){
						$sender->sendMessage(C::RED."Discord message wasn't sent.");
					}
				}
			}
			else{
				$sender->sendMessage(C::RED."Sorry, but the owner disabled this option.");
			}
		}
	return true;
	}
	
	private function setvars(){
    	$this->saveDefaultConfig();
	    if(key_exists("version", $this->getConfig()->getAll())){
	       if($this->configversion !== $this->getConfig()->get("Version")){
	           $this->getLogger()->critical("Please update your config!");
	           $this->setEnabled(false);
	           return;
           }
        }
        else{
            $this->getLogger()->critical("Please update your config!");
            $this->setEnabled(false);
            return;
        }
        foreach ($this->getConfig()->getAll() as $item){
            if(!isset($item) OR ""){
                $this->getLogger()->info("Please edit your config");
                $this->setEnabled(false);
                return;
            }
        }
		$this->reloadConfig();
		$this->webhook = $this->getConfig()->get("webhook_url");
		$this->username = $this->getConfig()->get("username");
		$this->startupopt = $this->getConfig()->get("start_message");
		$this->shutdownopt = $this->getConfig()->get("shutdown_message");
		$this->joinopt = $this->getConfig()->get("join_message");
		$this->quitopt = $this->getConfig()->get("quit_message");
		$this->deathopt = $this->getConfig()->get("death_message");
		$this->debugopt = $this->getConfig()->get("debug");
		$this->commandopt = $this->getConfig()->get("command");
		$this->chatformat = $this->getConfig()->get("chat_format");
		$this->chatprefix = $this->getConfig()->get("chat_prefix");
		$this->chatopt = $this->getConfig()->get("chat");
		
			//Some statements
			if($this->getConfig()->get("chat_username") === "0"){
				$this->chatuser = $this->username;
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

	public function notify($player, $result){
        if($player === "console"){ //If no player is specified
            return;
        }
        else{
            if($result["success"]) {
                $player->sendMessage(C::AQUA."[Discord-MCPE] ".C::GREEN."Discord message was send!");
                return;
            }
            else{
                if($this->getConfig()->get("debug")){
                    $this->getLogger()->error(C::RED."Error: ".$result["Error"]);
                }
                else{
                    $this->getLogger()->warning(C::RED."Something strange happened. Set debug in config to true to get error message");
                }
                $player->sendMessage(C::AQUA."[Discord-MCPE] ".C::GREEN."Discord message wasn't send!");
            }
        }
    }

    public function sendMessage($webhook, $message, $player = "console", $username = null){
	    if(!isset($username)){
	        $username = $this->username;
        }
	    $curlopts = [
	        "content" => $message,
            "username" => $username
        ];
        $this->getServer()->getScheduler()->scheduleAsyncTask(new Tasks\SendTaskAsync($player, $webhook, $curlopts));
    }
}