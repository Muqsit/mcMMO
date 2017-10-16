<?php
namespace muqsit\mcMMO\skills;

use pocketmine\block\{Leaves, Wood};
use pocketmine\event\player\PlayerInteractEvent;

class SkillManager{

    /** @var array */
    private $data;

    /** @var bool */
    private $hasUpdate = false;

    /** @var Woodcutting */
    private $woodcutting;

    public function __construct(array $data = []){
        $this->data = $data;
    }

    public function getRawData() : array{
        return $this->data;
    }

    /**
     * Handles interactions with activated
     * items and calls the appropriate
     * skill functions.
     *
     * @param PlayerInteractEvent $event
     */
    public function handleReadyItemInteract(PlayerInteractEvent $event){
        $item = $event->getItem();
        $block = $event->getBlock();
        if($item->isAxe() && $block instanceof Wood){
            if(!$this->getWoodcutting()->handleActivation($event->getPlayer(), $error) && isset($error)){
                $event->getPlayer()->sendMessage($error);
            }
            return;
        }
    }

    /**
     * Handles interactions caused by
     * unactivated items and calls the
     * appropriate skill functions.
     *
     * @param PlayerInteractEvent $event
     */
    public function handleUnreadyItemInteract(PlayerInteractEvent $event){
        $item = $event->getItem();
        $block = $event->getBlock();
        if($item->isAxe() && $block instanceof Leaves){
            $this->getWoodCutting()->handleLeafBlower($event->getPlayer(), $block, $item);
            return;
        }
    }

    /**
     * Returns woodcutting skill class.
     *
     * @return Woodcutting
     */
    public function getWoodcutting() : Woodcutting{
        $data = $this->data["woodcutting"] ?? [0, 0];
        return $data instanceof Woodcutting ? $data : $this->data["woodcutting"] = new Woodcutting(...$data);
    }

    /**
     * Only set to true if any get{BaseSkill}
     * function was ever called from this class.
     *
     * @param bool $value
     */
    public function setHasUpdate(bool $value = true){
        $this->hasUpdate = $value;
    }

    public function getData() : array{
        $data = $this->data;

        if(!$this->hasUpdate){
            return $data;
        }

        foreach($data as &$skill){
            if($skill instanceof BaseSkill){
                $skill = $skill->getData();
            }
        }

        return $data;
    }
}