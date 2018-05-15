<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\Skill;

use pocketmine\Player;

class ExcavationCommand extends SkillCommand{

	public function getSkillId() : int{
		return self::EXCAVATION;
	}

	public function getHelpMessage(int $page) : string{
		return "";
	}

	public function getSkillEffects(Player $player, Skill $skill) : string{
		return "";
	}

	public function getSkillStats(Player $player, Skill $skill) : string{
		return "";
	}
}