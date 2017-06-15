<?php
/**
 * Created by PhpStorm.
 * User: Roelof Dell
 * Date: 15-6-2017
 * Time: 22:00
 */

namespace Niekert\DiscordMCPE\Utils;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;

class ConfigUpdater extends PluginTask
{
    private $main;
    public function __construct($main)
    {
        parent::__construct($main);
    }

    public function onRun($currentTick)
    {
        $this->getOwner()->saveDefaultConfig();
        $config = new Config($this->getOwner()->getDataFolder(). "config.yml", Config::YAML, array());
        $version = $this->getOwner()->getDescription()->getVersion();
        if($config->get("Version") != $version){
            rename($this->getOwner()->getDataFolder()."config.yml", $this->getOwner()->getDataFolder()."oldconfig.yml");
            $this->getOwner()->saveDefaultConfig();
            $oldconfig = new Config($this->getOwner()->getDataFolder(). "config.yml", Config::YAML, array());
            foreach ($oldconfig->getAll() as $item) {
                $config->set($item, $oldconfig->get($item));
            }
            $config->set("Version", $version);
            $config->save();
        }
    }
}