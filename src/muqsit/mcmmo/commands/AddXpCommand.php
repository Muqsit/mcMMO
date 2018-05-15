<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\skills\SkillManager;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class AddXpCommand extends McMMOCommand{

	public function onCommand(CommandSender $sender, Command $cmd, string $commandLabel, array $args) : bool{
		$args_c = count($args);
		if($args_c !== 2 && $args_c !== 3){
			$sender->sendMessage(TextFormat::RED . "Usage /addxp <player> <skill> [xp=1]");
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

		$xp = (int) ($args[2] ?? 1);
		if($xp < 1){
			$sender->sendMessage(TextFormat::RED . "xp must be numeric and > 0, got '" . $args[2] . "'");
			return false;
		}

		$skill_manager = $this->getPlugin()->getSkillManager($player);
		$skill_name = $skill_manager->getSkill($skill)->getName();
		$skill_manager->addSkillXp($skill, $xp);

		$player->sendMessage(TextFormat::GREEN . "You were awarded " . number_format($xp) . " experience in " . $skill_name . "!");
		$sender->sendMessage(TextFormat::RED . $skill_name . " has been modified for " . $player->getName() . ".");
		return true;
	}
}