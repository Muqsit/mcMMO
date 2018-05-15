<?php
namespace muqsit\mcmmo\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

class McAbilityCommand extends McMMOCommand{

	protected function init() : void{
		$this->setFlag(McMMOCommand::FLAG_NO_CONSOLE);
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $commandLabel, array $args) : bool{
		$manager = $this->getPlugin()->getSkillManager($sender);
		$can_use_abilities = !$manager->canUseAbilities();
		$manager->setCanUseAbilities($can_use_abilities);
		$sender->sendMessage("Ability use togged " . ($can_use_abilities ? TextFormat::GREEN . "on" : TextFormat::RED . "off"));
		return true;
	}
}