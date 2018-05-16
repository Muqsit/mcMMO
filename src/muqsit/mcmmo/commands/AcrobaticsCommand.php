<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\Skill;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AcrobaticsCommand extends SkillCommand{

	public function getSkillId() : int{
		return self::ACROBATICS;
	}

	public function getSkillEffects(Player $player, Skill $skill) : string{
		return TextFormat::DARK_AQUA . "Roll: " . TextFormat::GREEN . "Reduces or Negates fall damage" . TextFormat::EOL .
		TextFormat::DARK_AQUA . "Gracefull Roll: " . TextFormat::GREEN . "Twice as effective as a normal Roll" . TextFormat::EOL .
		TextFormat::DARK_AQUA . "Dodge: " . TextFormat::GREEN . "Reduce attack damage by half";
	}

	public function getSkillStats(Player $player, Skill $skill) : string{
		return TextFormat::RED . "Roll Chance: " . TextFormat::YELLOW . sprintf("%.2f", $skill->getRollChance()) . "%" . TextFormat::EOL .
		TextFormat::RED . "Graceful Roll Chance: " . TextFormat::YELLOW . sprintf("%.2f", $skill->getGracefulRollChance()) . "%" . TextFormat::EOL .
		TextFormat::RED . "Dodge Chance: " . TextFormat::YELLOW . sprintf("%.2f", $skill->getDodgeChance()) . "%";
	}
}