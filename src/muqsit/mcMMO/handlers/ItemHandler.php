<?php
namespace muqsit\mcMMO\handlers;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class ItemHandler{

    const READY_TIMEOUT = 5;//seconds, time after which an item auto-deactivates

    const FIST = 0;
    const AXE = 1;
    const PICKAXE = 2;
    const SHOVEL = 3;
    const HOE = 4;
    const SWORD = 5;
    const SHEARS = 6;

    const ITEM_NAME = [
        self::FIST => "Fist",
        self::AXE => "Axe",
        self::PICKAXE => "Pickaxe",
        self::SHOVEL => "Shovel",
        self::HOE => "Hoe",
        self::SWORD => "Sword",
        self::SHEARS => "Shears",
    ];

    /** @var array */
    private $ready = [];

    /**
     * Returns whether the item can be
     * activated.
     *
     * @return bool
     */
    public function canReady(Item $item) : bool{
        return $item->isNull() || $item->isAxe() || $item->isPickaxe() || $item->isShovel() || $item->isHoe() || $item->isSword() || $item->isShears();
    }

    /**
     * Returns whether the item is
     * activated.
     *
     * @param Player $player
     * @param Item $item
     * @param null $type
     *
     * @return bool
     */
    public function isReady(Player $player, Item $item, &$type = null) : bool{
        if($item->isNull()){
            $type = self::FIST;
            return isset($this->ready[$player->getId()][self::FIST]);
        }
        if($item->isAxe()){
            $type = self::AXE;
            return isset($this->ready[$player->getId()][self::AXE]);
        }
        if($item->isPickaxe()){
            $type = self::PICKAXE;
            return isset($this->ready[$player->getId()][self::PICKAXE]);
        }
        if($item->isShovel()){
            $type = self::SHOVEL;
            return isset($this->ready[$player->getId()][self::SHOVEL]);
        }
        if($item->isHoe()){
            $type = self::HOE;
            return isset($this->ready[$player->getId()][self::HOE]);
        }
        if($item->isSword()){
            $type = self::SWORD;
            return isset($this->ready[$player->getId()][self::SWORD]);
        }
        if($item->isShears()){
            $type = self::SHEARS;
            return isset($this->ready[$player->getId()][self::SHEARS]);
        }
        return false;
    }

    /**
     * Deactivates an activated item.
     *
     * @param Player $player
     * @param int $itemType
     */
    public function clearReady(Player $player, int $itemType){
        if(isset($this->ready[$player->getId()][$itemType])){
            unset($this->ready[$player->getId()][$itemType]);
            $player->sendMessage(TextFormat::GRAY."**YOU LOWER YOUR ".strtoupper(self::ITEM_NAME[$itemType])."**");
        }
    }

    /**
     * Handles all activation criteria and
     * activates the item if the criteria
     * for activation is met.
     *
     * @param Player $player
     * @param Item $item
     */
    public function handleReadiness(Player $player, Item $item){
        if($this->canReady($item) && !$this->isReady($player, $item, $type)){
            if(!isset($this->ready[$player->getId()])){
                $this->ready[$player->getId()] = [];
            }
            $this->ready[$player->getId()][$type] = true;
            $this->scheduleReady($player, $item, $type);
        }
    }

    /**
     * Schedules a delayed task that
     * deactivates an activated item.
     *
     * @param Player $player
     * @param Item $item
     * @param int $itemType
     */
    private function scheduleReady(Player $player, Item $item, int $itemType){
        $player->getServer()->getScheduler()->scheduleDelayedTask(new class($player, $itemType, $this) extends Task{

            /** @var Player */
            private $player;

            /** @var int */
            private $type;

            /** @var ItemHandler */
            private $itemHandler;

            public function __construct(Player $player, int $itemType, ItemHandler $itemHandler){
                $this->player = $player;
                $this->itemType = $itemType;
                $this->itemHandler = $itemHandler;

                $player->sendMessage(TextFormat::GREEN."**YOU READY YOUR ".strtoupper(ItemHandler::ITEM_NAME[$itemType])."**");
            }

            public function onRun(int $tick){
                $this->itemHandler->clearReady($this->player, $this->itemType);
            }
        }, self::READY_TIMEOUT * 20);
    }
}