<?php
namespace muqsit\mcmmo\skills\tasks;

use muqsit\mcmmo\skills\SkillManager;

use pocketmine\utils\TextFormat;

class AbilityCooldownNotifyTask extends SkillTask{

	public function onRun(int $tick) : void{
		$skill_manager = $this->getSkillManager();
		$skill_manager->getPlayer()->sendMessage(TextFormat::GREEN . "Your " . TextFormat::YELLOW . $this->getSkill()->getName() . TextFormat::GREEN . " ability is refreshed!");
		$skill_manager->setTaskAsCompleted(SkillManager::TASK_ABILITY_COOLDOWN_NOTIFY);
	}
}