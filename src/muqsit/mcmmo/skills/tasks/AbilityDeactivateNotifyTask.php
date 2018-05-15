<?php
namespace muqsit\mcmmo\skills\tasks;

use muqsit\mcmmo\skills\SkillManager;

use pocketmine\utils\TextFormat;

class AbilityDeactivateNotifyTask extends SkillTask{

	public function onRun(int $tick) : void{
		$skill_manager = $this->getSkillManager();
		$skill_manager->getPlayer()->sendMessage(TextFormat::RED . "**" . $this->getSkill()->getName() . " has worn off**");
		$skill_manager->setTaskAsCompleted(SkillManager::TASK_ABILITY_DEACTIVATE_NOTIFY);
	}
}