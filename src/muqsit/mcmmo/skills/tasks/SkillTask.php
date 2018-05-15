<?php
namespace muqsit\mcmmo\skills\tasks;

use muqsit\mcmmo\skills\Skill;
use muqsit\mcmmo\skills\SkillManager;

use pocketmine\scheduler\Task;

abstract class SkillTask extends Task{

	/** @var SkillManager */
	private $skill_manager;

	/** @var int */
	private $skillId;

	public function __construct(SkillManager $manager, int $skillId = -1){
		$this->skill_manager = $manager;
		$this->skillId = $skillId;
	}

	public function getSkillManager() : SkillManager{
		return $this->skill_manager;
	}

	public function getSkillId() : int{
		return $this->skillId;
	}

	public function getSkill() : ?Skill{
		return $this->skill_manager->getSkill($this->skillId);
	}
}