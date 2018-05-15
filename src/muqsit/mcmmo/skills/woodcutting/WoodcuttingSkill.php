<?php
namespace muqsit\mcmmo\skills\woodcutting;

use muqsit\mcmmo\skills\Skill;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class WoodcuttingSkill extends Skill{

	const SKILL_ID = self::WOODCUTTING;

	public static function getListenerClass() : ?string{
		return WoodcuttingListener::class;
	}

	public static function getItemIdentifies() : ?array{
		return [
			Item::IRON_AXE,
			Item::WOODEN_AXE,
			Item::STONE_AXE,
			Item::DIAMOND_AXE,
			Item::GOLDEN_AXE
		];
	}


	public function getName() : string{
		return "Woodcutting";
	}

	public function getShortDescription() : string{
		return "Chopping down trees";
	}

	public function getAbilityName() : string{
		return "Tree Feller";
	}

	public function getDoubleDropChance() : float{
		return min(100, $this->getLevel() / 10);
	}

	public function onActivateAbility(Player $player) : void{
	}
}