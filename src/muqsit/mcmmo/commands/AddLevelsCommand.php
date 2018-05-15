<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\SkillManager;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class AddLevelsCommand extends McMMOCommand{

	public function onCommand(CommandSender $sender, Command $cmd, string $commandLabel, array $args) : bool{
		$args_c = count($args);
		if($args_c !== 2 && $args_c !== 3){
			$sender->sendMessage(TextFormat::RED . "Usage /addlevels <player> <skill> [level=1]");
			return true;
		}

		$player = $this->getPlugin()->getServer()->getPlayer($args[0]);
		if($player === null){
			$sender->sendMessage(TextFormat::RED . "Player '" . $args[0] . "' not found.");
			return false;
		}

		$skill = SkillManager::getSkillIdByName($args[1]);
		if($skill === null){
			$sender->sendMessage(TextFormat::RED . "Invalid skill '" . $args[1] . "' given.");
			return false;
		}

		$levels = (int) ($args[2] ?? 1);
		if($levels < 1){
			$sender->sendMessage(TextFormat::RED . "level must be numeric and > 0, got '" . $args[2] . "'");
			return false;
		}

		$skill = $this->getPlugin()->getSkillManager($player)->getSkill($skill);
		$skill_name = $skill->getName();

		if(!$skill->addLevels($levels)){
			$sender->sendMessage(TextFormat::RED . "Failed to modify " . $player->getName() . "'s " . $skill_name . " skill.");
			return false;
		}

		$player->sendMessage(TextFormat::GREEN . "You were awarded " . number_format($levels) . " levels in " . $skill_name . "!");
		$sender->sendMessage(TextFormat::RED . $skill_name . " has been modified for " . $player->getName() . ".");
		return true;
	}
}