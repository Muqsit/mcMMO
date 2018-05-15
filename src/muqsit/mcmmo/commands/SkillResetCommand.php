<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\SkillManager;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;

class SkillResetCommand extends McMMOCommand{

	public function onCommand(CommandSender $sender, Command $cmd, string $commandLabel, array $args) : bool{
		$args_c = count($args);
		if($args_c !== 2 && $args_c !== 3){
			$sender->sendMessage(TextFormat::RED . "Usage /skillreset <player> <skill>");
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

		$skill = $this->getPlugin()->getSkillManager($player)->getSkill($skill);
		$skill->reset();
		$skill_name = $skill->getName();

		$player->sendMessage(TextFormat::GREEN . "Your " . $skill_name . " skill level has been reset successfully!");
		$sender->sendMessage(TextFormat::RED . $skill_name . " has been modified for " . $player->getName() . ".");
		return true;
	}
}