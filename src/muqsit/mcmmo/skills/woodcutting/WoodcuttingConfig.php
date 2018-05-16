<?php
namespace muqsit\mcmmo\skills\woodcutting;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Leaves;
use pocketmine\block\Leaves2;
use pocketmine\block\Wood;
use pocketmine\block\Wood2;
use pocketmine\item\Axe;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class WoodcuttingConfig{

	const MINIMUM_LEAFBLOWER_LEVEL = 100;

	const TREE_FELLER_DIRECTIONS = [
		[1, 0, 0], [-1, 0, 0], [0, 0, 1], [0, 0, -1], [0, 1, 0]
	];

	/** @var int[] */
	private $values = [];

	public function __construct(){
		$this->setDefaults();
	}

	private function setDefaults() : void{
		$this->set(Block::get(Block::WOOD, Wood::BIRCH), 90);
		$this->set(Block::get(Block::WOOD, Wood::OAK), 70);
		$this->set(Block::get(Block::WOOD, Wood::JUNGLE), 100);
		$this->set(Block::get(Block::WOOD, Wood::SPRUCE), 80);

		$this->set(Block::get(Block::WOOD2, Wood2::ACACIA), 90);
		$this->set(Block::get(Block::WOOD2, Wood2::DARK_OAK), 90);

		$this->set(Block::get(Block::BROWN_MUSHROOM_BLOCK, 10), 70);
		$this->set(Block::get(Block::BROWN_MUSHROOM_BLOCK, 15), 70);
		$this->set(Block::get(Block::RED_MUSHROOM_BLOCK, 10), 70);
		$this->set(Block::get(Block::RED_MUSHROOM_BLOCK, 15), 70);
	}

	public function set(Block $block, int $xpreward) : void{
		$this->values[BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage())] = $xpreward;
	}

	public function isRightTool(Item $item) : bool{
		return $item instanceof Axe;
	}

	public function isLeaf(Block $block) : bool{
		return $block instanceof Leaves || $block instanceof Leaves2;
	}

	private function treeFellerSearch(Vector3 $pos, Level $level, int $logId, int $leafId = -1) : \Generator{
		foreach(self::TREE_FELLER_DIRECTIONS as [$xOffset, $yOffset, $zOffset]){
			$blockId = $level->getBlockIdAt($pos->x + $xOffset, $pos->y + $yOffset, $pos->z + $zOffset);
			if($blockId === $logId){
				$pos->x += $xOffset;
				$pos->y += $yOffset;
				$pos->z += $zOffset;
				foreach($this->treeFellerSearch($pos->asVector3(), $level, $leafId) as $leaf_pos){
					$level->setBlockIdAt($leaf_pos->x, $leaf_pos->y, $leaf_pos->z, Block::AIR);
					$level->setBlockDataAt($leaf_pos->x, $leaf_pos->y, $leaf_pos->z, 0);
				}
				yield from $this->treeFellerSearch($pos, $level, $logId, $leafId);
			}elseif($blockId === $leafId){
				$level->setBlockIdAt($pos->x, $pos->y, $pos->z, Block::AIR);
				$level->setBlockDataAt($pos->x, $pos->y, $pos->z, 0);
			}else{
				yield $pos;
			}
		}
	}

	public function getDrops(Player $player, Item $item, Block $block, int $skill_level, bool $has_ability, &$xpreward = null) : array{
		$xpreward = 0;
		$drops = $block->getDrops($item);

		if($this->isRightTool($item)){
			if(isset($this->values[$index = BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage())])){
				$xpreward = $this->values[$index];
				$multiplier = ($skill_level > 999 || mt_rand(1, 1000) <= $skill_level) ? 2 : 1;

				if($has_ability){
					$level = $player->getLevel();
					$i = 0;
					foreach($this->treeFellerSearch($block->asVector3(), $level, $block->getId(), $block instanceof Wood ? Block::LEAVES : Block::LEAVES2) as $pos){
						$level->setBlockIdAt($pos->x, $pos->y, $pos->z, Block::AIR);
						$level->setBlockDataAt($pos->x, $pos->y, $pos->z, 0);
						++$i;
					}

					$multiplier += $i;
					$xpreward *= $i;
				}

				if($multiplier > 1){
					foreach($drops as $drop){
						$drop->setCount($drop->getCount() * $multiplier);
					}
				}
			}elseif(mt_rand(1, 20) === 1 && $this->isLeaf($block) && $skill_level >= WoodcuttingConfig::MINIMUM_LEAFBLOWER_LEVEL){
				$sapling = $block->getSaplingItem();
				$drops_sapling = false;

				foreach($drops as $drop){
					if($drop->equals($sapling, false, false)){
						$drops_sapling = true;
						break;
					}
				}

				if(!$drops_sapling){
					$drops[] = $sapling;
				}
			}
		}

		return $drops;
	}
}