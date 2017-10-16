<?php
namespace muqsit\mcMMO;

use muqsit\mcMMO\commands\mcMMOCommand;
use muqsit\mcMMO\handlers\HandlerManager;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class mcMMO extends PluginBase{

    /** @var mcMMO */
    private static $instance;

    /** @var ProviderManager */
    private $providerManager;

    /** @var HandlerManager */
    private $handlerManager;

    /** @var mcMMOPlayer[] */
    private $players = [];

    public function onEnable(){
        self::$instance = $this;
        $this->getServer()->getLogger()->notice("Enabled mcMMO");

        $this->saveResource("database.yml");
        $this->initProvider();

        $this->handlerManager = new HandlerManager();

        mcMMOCommand::registerCommands($this);
        new EventListener($this);
    }

    public static function getInstance() : mcMMO{
        return self::$instance;
    }

    public function onDisable(){
        $this->getProvider()->save();
    }

    private function initProvider(){
        $path = $this->getDataFolder()."mcmmo.users";
        $this->provider = new Provider($path);
    }

    public function getProvider() : Provider{
        return $this->provider;
    }

    public function getHandlerManager() : HandlerManager{
        return $this->handlerManager;
    }

    public function addPlayer(Player $player) : bool{
        $id = $player->getId();
        if(!isset($this->players[$id])){
            $this->players[$id] = new mcMMOPlayer($this->getProvider(), $player);
            return true;
        }
        return false;
    }

    public function removePlayer(Player $player) : bool{
        $id = $player->getId();
        if(isset($this->players[$id])){
            $this->players[$id]->save();
            unset($this->players[$id]);
            return true;
        }
        return false;
    }

    public function getPlayer(int $id) : ?mcMMOPlayer{
        return $this->players[$id] ?? null;
    }

    public function getPlayerByName(string $name) : ?mcMMOPlayer{
        $player = $this->getServer()->getPlayerExact($name);
        if($player !== null){
            return $this->getPlayer($player->getId());
        }
        return null;
    }
}