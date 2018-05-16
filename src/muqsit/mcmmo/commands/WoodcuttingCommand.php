<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\Skill;
use muqsit\mcmmo\skills\woodcutting\WoodcuttingConfig;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

class WoodcuttingCommand extends SkillCommand{

	public function getSkillId() : int{
		return self::WOODCUTTING;
	}

	public function getSkillEffects(Player $player, Skill $skill) : string{
		return TextFormat::DARK_AQUA . $skill->getAbilityName() . " (ABILITY): " . TextFormat::GREEN . "Make trees explode" . TextFormat::EOL .
		TextFormat::DARK_AQUA . "Leaf Blower: " . TextFormat::GREEN . "Blow Away Leaves" . TextFormat::EOL .
		TextFormat::DARK_AQUA . "Double Drops: " . TextFormat::GREEN . "Double the normal loot";
	}

	public function getSkillStats(Player $player, Skill $skill) : string{
		$result = "";
		if($skill->getLevel() < WoodcuttingConfig::MINIMUM_LEAFBLOWER_LEVEL){
			$result .= TextFormat::GRAY . "LOCKED UNTIL " . WoodcuttingConfig::MINIMUM_LEAFBLOWER_LEVEL . "+ SKILL (LEAF BLOWER)" . TextFormat::EOL;
		}

		$result .= TextFormat::RED . "Double Drop Chance: " . TextFormat::YELLOW . sprintf("%.2f", $skill->getDoubleDropChance()) . "%" . TextFormat::EOL;
		return $result . TextFormat::RED . $skill->getName() . " Length: " . TextFormat::YELLOW . $skill->getAbilityDuration() . "s";
	}
}