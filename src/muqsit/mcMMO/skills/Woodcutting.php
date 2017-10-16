<?php
namespace muqsit\mcMMO\skills;

use pocketmine\block\{Block, Leaves, Wood, Wood2};
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\level\sound\PopSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Woodcutting extends ActivatableSkill{

    const SKILL_NAME = "Woodcutting";

    const LEAF_BLOWER_LVL_REQUIREMENT = 100;

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

    public function __construct(int $xp, int $level){
        $this->xp = $xp;
        $this->level = $level;
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
                $this->activateTreeFeller($player, $block, $event->getItem());
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
     * @param Player $player
     * @param Wood $block
     * @param Item $item
     */
    private function activateTreeFeller(Player $player, Wood $block, Item $item){
        $level = $block->getLevel();
        $block = $block->getSide(Vector3::SIDE_UP);
        $xp = 0;
        $damage = $item->getDamage();

        while($block instanceof Wood){
            $xp += self::XP_GAINS[$block->getId()][$block->getDamage()] ?? 0;
            //Don't use Level::useBreakOn() because it will call BlockBreakEvent which would call self::handleBlockBreak() and thus potentially cause segmentation faults.
            $level->setBlockIdAt($block->x, $block->y, $block->z, Block::AIR);
            foreach($block->getDrops($item) as $drop){
                if($this->canDoubleDrop()){
                    $drop->setCount($drop->getCount() * 2);
                }
                $level->dropItem($block, $drop);
            }
            $block = $block->getSide(Vector3::SIDE_UP);
            $damage++;
        }

        $item->setDamage($damage);
        if($xp !== 0){
            $this->applyXpGain($player, $xp);
        }
    }

    public function handleLeafBlower(Player $player, Leaves $block, Item $item){
        if($this->getLevel() >= self::LEAF_BLOWER_LVL_REQUIREMENT){
            $ev = new BlockBreakEvent($player, $block, $item, true);
            $player->getServer()->getPluginManager()->callEvent($ev);
            if(!$ev->isCancelled()){
                $block->getLevel()->useBreakOn($block, $item, $player);
                $player->getLevel()->addSound(new PopSound($player), [$player]);
            }
        }
    }

    public function handleActivation(Player $player, &$error = null) : bool{
        if(parent::handleActivation($player, $error)){
            $player->sendMessage(TextFormat::GREEN."**TREE FELLER ACTIVATED**");
            $this->sendDelayedMessage($player, TextFormat::RED."**Tree Feller has worn off**", $this->getTreeFellerDuration());
            $this->sendDelayedMessage($player, TextFormat::GREEN."Your ".TextFormat::YELLOW."Tree Feller ".TextFormat::GREEN."ability is refreshed!", $this->getCooldown());
            return true;
        }
        return false;
    }

    /**
     * Duration (in seconds) of how long the
     * tree feller skill would last.
     *
     * @return int
     */
    public function getTreeFellerDuration() : int{
        return intval(2 + floor($this->getLevel() / 50));
    }

    /**
     * Chance to double the drops' count.
     *
     * @return float
     */
    public function getDoubleDropRate() : float{
        return min(100.0, $this->getLevel() * 0.1);
    }

    /**
     * Returns random boolean value
     * that when true, doubles the drops'
     * count.
     *
     * @return bool
     */
    private function canDoubleDrop() : bool{
        return mt_rand($this->getDoubleDropRate(), 100.0) === 100.0;
    }
}