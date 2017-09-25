<?php

namespace Niekert\DiscordMCPE\Tasks;

use Niekert\DiscordMCPE\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SendTaskAsync extends AsyncTask
{
    private $player, $webhook, $curlopts;

    /**
     * SendTaskAsync constructor.
     * @param $player
     * @param $webhook
     * @param $curlopts
     */
    public function __construct($player, $webhook, $curlopts)
    {
        $this->player = $player;
        $this->webhook = $webhook;
        $this->curlopts = $curlopts;
    }

    public function onRun()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->webhook);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(unserialize($this->curlopts)));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $curlerror = curl_error($curl);

        //Messy code incoming
        $responsejson = json_decode($response, true);
        $success = false;
        $error = "IDK What happened";
        if($curlerror != ""){
            $error = $curlerror;
        }
        elseif (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 204) {
            $error = $responsejson['message'];
        }
        elseif (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 204 OR $response === ""){
            $success = true;
        }
        $result = ["Response" => $response, "Error" => $error, "success" => $success];
        $this->setResult($result, true);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server)
    {
        $plugin = $server->getPluginManager()->getPlugin('Discord-MCPE');
        if(!$plugin instanceof Main){
            return;
        }
        if(!$plugin->isEnabled()){
            return;
        }
        $plugin->notify($this->player, $this->getResult());
    }
}