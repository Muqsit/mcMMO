<?php
namespace muqsit\mcmmo\skills\excavation;

use muqsit\mcmmo\skills\Skill;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class ExcavationSkill extends Skill{

	const SKILL_ID = self::EXCAVATION;

	public static function getListenerClass() : ?string{
		return ExcavationListener::class;
	}

	public static function getItemIdentifies() : ?array{
		return [
			Item::IRON_SHOVEL,
			Item::WOODEN_SHOVEL,
			Item::STONE_SHOVEL,
			Item::DIAMOND_SHOVEL,
			Item::GOLDEN_SHOVEL
		];
	}


	public function getName() : string{
		return "Excavation";
	}

	public function getShortDescription() : string{
		return "Digging and finding treasures";
	}

	public function getAbilityName() : string{
		return "Giga Drill Breaker";
	}

	public function getAbilityDuration() : int{
		return 2 * floor($this->getLevel() / 50) + 2;
	}

	public function getAbilityCooldown() : int{
		return 250;
	}

	public function onActivateAbility(Player $player) : void{
		$player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), $this->getAbilityDuration() * 20, 3, false));
	}
}