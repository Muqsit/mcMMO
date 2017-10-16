<?php
namespace muqsit\mcMMO;

use muqsit\mcMMO\skills\SkillManager;

use pocketmine\Player;

class mcMMOPlayer{

    /** @var Player */
    private $player;

    /** @var Provider */
    private $provider;

    /** @var string|null */
    private $uuid;

    public function __construct(Provider $provider, Player $player){
        $this->player = $player;
        $this->provider = $provider;
        $this->uuid = $player->getUniqueId()->toString();
        $provider->getUserData($this->uuid, true)->setHasUpdate();
    }

    public function getPlayer() : Player{
        return $this->player;
    }

    public function getSkillManager() : SkillManager{
        return $this->provider->getUserData($this->uuid);
    }
}