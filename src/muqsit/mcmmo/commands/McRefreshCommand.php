<?php
namespace muqsit\mcmmo\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class McRefreshCommand extends McMMOCommand{

	public function onCommand(CommandSender $sender, Command $cmd, string $commandLabel, array $args) : bool{
		if($sender instanceof ConsoleCommandSender && !isset($args[0])){
			$sender->sendMessage(TextFormat::RED . "Usage /mcrefresh <player>");
			return false;
		}

		$player = isset($args[0]) ? $this->getPlugin()->getServer()->getPlayer($args[0]) : $sender;
		if($player === null){
			$sender->sendMessage(TextFormat::RED . "Player '" . $args[0] . "' not found.");
			return false;
		}

		$manager = $this->getPlugin()->getSkillManager($player);
		foreach($manager->getSkillTree() as $skill){
			$skill->refresh();
		}

		$player->sendMessage(TextFormat::GREEN . "**ABILITIES REFRESHED!**");
		if(isset($args[0])){
			$sender->sendMessage(TextFormat::RED . $player->getName() . "'s cooldown have been refreshed.");
		}
		return true;
	}
}