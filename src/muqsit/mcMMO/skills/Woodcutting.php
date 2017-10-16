<?php
namespace muqsit\mcMMO\skills;

use pocketmine\block\{Block, Wood, Wood2};
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Woodcutting extends ActivatableSkill{

    const BLOCKS = [Block::LOG, Block::LOG2];

    const XP_GAINS = [
        Block::LOG => [
            Wood::OAK => 70,
            Wood::SPRUCE => 80,
            Wood::BIRCH => 90,
            Wood::JUNGLE => 100
        ],
        Block::LOG2 => [
            Wood2::ACACIA => 90,
            Wood2::DARK_OAK => 90
        ]
    ];

    const XP_GAIN_THRESHOLD = 20000;

    public function __construct(int $xp){
        $this->xp = $xp;
    }

    /**
     * Handles woodcutting activations and
     * experience gains.
     *
     * @param BlockBreakEvent $event
     */
    public function handleBlockBreak(BlockBreakEvent $event){
        $block = $event->getBlock();
        if(in_array($block->getId(), self::BLOCKS, true)){
            $player = $event->getPlayer();

            if($this->isActivationOngoing()){
                $this->activateTreeFeller($block, $event->getItem());
            }

            $xp = self::XP_GAINS[$block->getId()][$block->getDamage()] ?? null;
            if($xp !== null){
                $this->applyXpGain($player, $xp);
            }
        }
    }

    /**
     * Activates the "Tree Feller" skill.
     *
     * @param Wood $block
     * @param Item $item
     */
    private function activateTreeFeller(Wood $block, Item $item){
        $level = $block->getLevel();
        $block = $block->getSide(Vector3::SIDE_UP);
        var_dump($block);
        while($block instanceof Wood){
            //Don't use Level::useBreakOn() because it will call BlockBreakEvent which would call self::handleBlockBreak() and thus potentially cause segmentation faults.
            $level->setBlockIdAt($block->x, $block->y, $block->z, Block::AIR);
            foreach($block->getDrops($item) as $drop){
                $level->dropItem($block, $drop);
            }
            $block = $block->getSide(Vector3::SIDE_UP);
        }
    }

    public function handleActivation(Player $player, &$error = null) : bool{
        if(parent::handleActivation($player, $error)){
            $player->sendMessage(TextFormat::GREEN."**TREE FELLER ACTIVATED**");
            $this->sendDelayedMessage($player, TextFormat::RED."**Tree Feller has worn off**");
            return true;
        }
        return false;
    }
}