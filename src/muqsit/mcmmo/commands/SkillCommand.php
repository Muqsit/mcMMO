<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\Skill;
use muqsit\mcmmo\skills\SkillIds;
use muqsit\mcmmo\skills\SkillManager;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class SkillCommand extends McMMOCommand implements SkillIds{

	public function init() : void{
		$this->setFlag(McMMOCommand::FLAG_NO_CONSOLE);
	}

	abstract public function getSkillId() : int;

	public function getFirstPage(Player $player) : string{
		$skill_manager = $this->getPlugin()->getSkillManager($player);
		$skill = $skill_manager->getSkill($this->getSkillId());
		$skill_name = $skill->getName();

		return TextFormat::RED . "-----[]" . TextFormat::GREEN . $skill_name . TextFormat::RED . "[]-----" . TextFormat::EOL .
			TextFormat::DARK_GRAY . "XP GAIN: " . TextFormat::WHITE . $skill->getShortDescription() . TextFormat::EOL .
			TextFormat::DARK_GRAY . "LVL: " . TextFormat::GREEN . $skill->getLevel() . TextFormat::DARK_AQUA . " XP" . TextFormat::YELLOW . "(" . TextFormat::GOLD . number_format($skill->getXp()) . TextFormat::YELLOW . "/" . TextFormat::GOLD . number_format($skill->getMaxLevelXp()) . TextFormat::YELLOW . ")" . TextFormat::EOL .
			TextFormat::RED . "-----[]" . TextFormat::GREEN . "EFFECTS" . TextFormat::RED . "[]-----" . TextFormat::EOL .
			$this->getSkillEffects($player, $skill) . TextFormat::EOL .
			TextFormat::RED . "-----[]" . TextFormat::GREEN . "YOUR STATS" . TextFormat::RED . "[]-----" . TextFormat::EOL .
			$this->getSkillStats($player, $skill) . TextFormat::EOL .
		TextFormat::DARK_AQUA . "Guide for " . $skill_name . " available - type /" . strtolower($skill_name) . " ? [page]";
	}

	abstract public function getHelpMessage(int $page) : string;

	abstract public function getSkillEffects(Player $player, Skill $skill) : string;

	abstract public function getSkillStats(Player $player, Skill $skill) : string;

	public function onCommand(CommandSender $sender, Command $cmd, string $commandLabel, array $args) : bool{
		if(isset($args[0]) && $args[0] === "?"){
			$page = (int) ($args[1] ?? 1);
			$sender->sendMessage($this->getHelpMessage($page));
			return true;
		}

		$sender->sendMessage($this->getFirstPage($sender));
		return true;
	}
}