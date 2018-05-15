<?php
namespace muqsit\mcmmo\skills\excavation;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\item\Shovel;
use pocketmine\Player;

class ExcavationConfig{

	const TYPE_XPREWARD = 0;
	const TYPE_SKILLREQ = 1;
	const TYPE_CHANCE = 2;
	const TYPE_DROPS = 3;

	/** @var array[] */
	private $values = [];

	public function __construct(){
		//TODO: Make things config.ymlable
		$this->setDefaults();
	}

	public function set(Block $block, int $xpreward = 0, int $skillreq = 0, ?array $drops = null) : void{
		$this->values[BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage())] = [
			ExcavationConfig::TYPE_XPREWARD => $xpreward,
			ExcavationConfig::TYPE_SKILLREQ => $skillreq,
			ExcavationConfig::TYPE_DROPS => $this->createDropsConfig($drops)
		];
	}

	public function copy(Block $block, Block ...$blocks) : void{
		$copy_index = BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage());
		foreach($blocks as $block){
			$block_index = BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage());
			$this->values[$block_index] = $this->values[$copy_index];
		}
	}

	public function addDrops(array $drops, Block ...$blocks) : void{
		$drops = $this->createDropsConfig($drops);
		if($drops !== null){
			foreach($blocks as $block){
				if(!isset($this->values[$index = BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage())])){
					throw new \InvalidArgumentException("Cannot modify block drops of an unconfigured block (" . get_class($block) . ")");
				}

				if(isset($this->values[$index][ExcavationConfig::TYPE_DROPS])){
					$this->values[$index][ExcavationConfig::TYPE_DROPS] = array_unique(array_merge($this->values[$index][ExcavationConfig::TYPE_DROPS], $drops), SORT_REGULAR);
				}else{
					$this->values[$index][ExcavationConfig::TYPE_DROPS] = $drops;
				}
			}
		}
	}

	private function createDropsConfig(?array $drops) : ?array{
		if(empty($drops)){
			return null;
		}

		$result = [];

		foreach($drops as [
			ExcavationConfig::TYPE_SKILLREQ => $skillreq,
			ExcavationConfig::TYPE_XPREWARD => $xpreward,
			ExcavationConfig::TYPE_CHANCE => $chance,
			ExcavationConfig::TYPE_DROPS => $drops
		]){
			$result[$skillreq][] = [
				ExcavationConfig::TYPE_XPREWARD => $xpreward,
				ExcavationConfig::TYPE_CHANCE => (int) $chance * 100,//$chance = percentage with a precision of 2
				ExcavationConfig::TYPE_DROPS => $drops
			];
		}

		return array_unique($result, SORT_REGULAR);
	}

	private function isRightTool(Item $item) : bool{
		return $item instanceof Shovel;
	}

	public function getDrops(Player $player, Item $item, Block $block, int $skill_level, bool $has_ability, &$xpreward = null) : array{
		$xpreward = 0;
		$multiplier = $has_ability ? 3 : 1;

		if($this->isRightTool($item) && isset($this->values[$index = BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage())])){
			$values = $this->values[$index];
			if($skill_level >= $values[ExcavationConfig::TYPE_SKILLREQ]){
				$xpreward = $values[ExcavationConfig::TYPE_XPREWARD] * $multiplier;
			}

			if(isset($values[ExcavationConfig::TYPE_DROPS])){
				foreach($values[ExcavationConfig::TYPE_DROPS] as $skillreq => $drops){
					if($skill_level >= $skillreq){
						foreach($drops as [
							ExcavationConfig::TYPE_XPREWARD => $xprew,
							ExcavationConfig::TYPE_CHANCE => $chance,
							ExcavationConfig::TYPE_DROPS => $drops
						]){
							$chance *= $multiplier;
							if(mt_rand($chance, 10000) <= $chance){
								$xpreward = $xprew * $multiplier;
								return $drops;
							}
						}
					}
				}
			}
		}

		return $block->getDrops($item);
	}

	private function setDefaults() : void{
		$this->set(Block::get(Block::GRASS), 40);
		$this->copy(Block::get(Block::GRASS),
			Block::get(Block::MYCELIUM), Block::get(Block::DIRT), Block::get(Block::GRAVEL),
			Block::get(Block::SAND), Block::get(Block::SAND, 1), Block::get(Block::CLAY_BLOCK),
			Block::get(Block::SOUL_SAND)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 25,
				ExcavationConfig::TYPE_XPREWARD => 80,
				ExcavationConfig::TYPE_CHANCE => 5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::GLOWSTONE_DUST)]
			]
		],
			Block::get(Block::GRASS), Block::get(Block::MYCELIUM), Block::get(Block::DIRT),
			Block::get(Block::SAND), Block::get(Block::SAND, 1)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 250,
				ExcavationConfig::TYPE_XPREWARD => 100,
				ExcavationConfig::TYPE_CHANCE => 1,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::EGG)]
			]
		],
			Block::get(Block::GRASS)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 250,
				ExcavationConfig::TYPE_XPREWARD => 3000,
				ExcavationConfig::TYPE_CHANCE => 0.05,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::RECORD_13)]//GOLD MUSIC DISC
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 250,
				ExcavationConfig::TYPE_XPREWARD => 3000,
				ExcavationConfig::TYPE_CHANCE => 0.05,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::RECORD_FAR)]//GREEN MUSIC DISC
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 350,
				ExcavationConfig::TYPE_XPREWARD => 1000,
				ExcavationConfig::TYPE_CHANCE => 0.13,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::DIAMOND)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 750,
				ExcavationConfig::TYPE_XPREWARD => 3000,
				ExcavationConfig::TYPE_CHANCE => 0.5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::CAKE)]
			]
		],
			Block::get(Block::GRASS), Block::get(Block::DIRT), Block::get(Block::GRAVEL),
			Block::get(Block::SAND), Block::get(Block::SAND, 1), Block::get(Block::CLAY_BLOCK)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 250,
				ExcavationConfig::TYPE_XPREWARD => 100,
				ExcavationConfig::TYPE_CHANCE => 0.1,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::APPLE)]
			]
		],
			Block::get(Block::GRASS), Block::get(Block::MYCELIUM)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 250,
				ExcavationConfig::TYPE_XPREWARD => 3000,
				ExcavationConfig::TYPE_CHANCE => 0.05,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::NAMETAG)]
			]
		],
			Block::get(Block::DIRT), Block::get(Block::GRASS), Block::get(Block::SAND),
			Block::get(Block::SAND, 1), Block::get(Block::GRAVEL), Block::get(Block::CLAY_BLOCK),
			Block::get(Block::MYCELIUM), Block::get(Block::SOUL_SAND)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 350,
				ExcavationConfig::TYPE_XPREWARD => 80,
				ExcavationConfig::TYPE_CHANCE => 1.33,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::DYE, 3)]//COCOA BEANS
			]
		],
			Block::get(Block::GRASS), Block::get(Block::MYCELIUM), Block::get(Block::DIRT)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 500,
				ExcavationConfig::TYPE_XPREWARD => 80,
				ExcavationConfig::TYPE_CHANCE => 0.5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::RED_MUSHROOM)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 500,
				ExcavationConfig::TYPE_XPREWARD => 80,
				ExcavationConfig::TYPE_CHANCE => 0.5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::BROWN_MUSHROOM)]
			]
		],
			Block::get(Block::GRASS), Block::get(Block::DIRT), Block::get(Block::MYCELIUM)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 75,
				ExcavationConfig::TYPE_XPREWARD => 30,
				ExcavationConfig::TYPE_CHANCE => 10,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::GUNPOWDER)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 175,
				ExcavationConfig::TYPE_XPREWARD => 30,
				ExcavationConfig::TYPE_CHANCE => 10,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::BONE)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 850,
				ExcavationConfig::TYPE_XPREWARD => 30,
				ExcavationConfig::TYPE_CHANCE => 0.5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::NETHERRACK)]
			]
		],
			Block::get(Block::GRAVEL)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 150,
				ExcavationConfig::TYPE_XPREWARD => 10,
				ExcavationConfig::TYPE_CHANCE => 1,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::SLIMEBALL)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 250,
				ExcavationConfig::TYPE_XPREWARD => 200,
				ExcavationConfig::TYPE_CHANCE => 5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::STRING)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 500,
				ExcavationConfig::TYPE_XPREWARD => 100,
				ExcavationConfig::TYPE_CHANCE => 0.1,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::BUCKET)]
			],
			[
				ExcavationConfig::TYPE_SKILLREQ => 500,
				ExcavationConfig::TYPE_XPREWARD => 100,
				ExcavationConfig::TYPE_CHANCE => 0.1,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::CLOCK)]
			]
		],
			Block::get(Block::CLAY_BLOCK)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 650,
				ExcavationConfig::TYPE_XPREWARD => 80,
				ExcavationConfig::TYPE_CHANCE => 0.5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::SOUL_SAND)]
			]
		],
			Block::get(Block::SAND)
		);

		$this->addDrops([
			[
				ExcavationConfig::TYPE_SKILLREQ => 850,
				ExcavationConfig::TYPE_XPREWARD => 100,
				ExcavationConfig::TYPE_CHANCE => 0.5,
				ExcavationConfig::TYPE_DROPS => [Item::get(Item::QUARTZ)]
			]
		],
			Block::get(Block::DIRT), Block::get(Block::SAND), Block::get(Block::SAND, 1),
			Block::get(Block::GRAVEL), Block::get(Block::MYCELIUM), Block::get(Block::SOUL_SAND)
		);
	}
}