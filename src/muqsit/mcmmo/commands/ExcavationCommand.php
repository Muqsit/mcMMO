<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\Skill;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ExcavationCommand extends SkillCommand{

	public function getSkillId() : int{
		return self::EXCAVATION;
	}

	public function getHelpMessage(int $page) : string{
		return "";
	}

	public function getSkillEffects(Player $player, Skill $skill) : string{
		return TextFormat::DARK_AQUA . $skill->getAbilityName() . " (ABILITY): " . TextFormat::GREEN . "3x Drop Rate, 3x EXP, +Speed" . TextFormat::EOL .
		TextFormat::DARK_AQUA . "Treasure Hunter: " . TextFormat::GREEN . "Ability to dig for treasure";
	}

	public function getSkillStats(Player $player, Skill $skill) : string{
		return TextFormat::RED . $skill->getName() . " Length: " . TextFormat::YELLOW . $skill->getAbilityDuration() . "s";
	}
}