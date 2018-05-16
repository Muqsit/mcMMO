<?php
namespace muqsit\mcmmo\skills\acrobatics;

use muqsit\mcmmo\skills\Skill;

class AcrobaticsSkill extends Skill{

	const SKILL_ID = self::ACROBATICS;

	public static function getListenerClass() : ?string{
		return AcrobaticsListener::class;
	}

	public function getName() : string{
		return "Acrobatics";
	}

	public function getShortDescription() : string{
		return "Falling";
	}

	public function getRollChance() : float{
		return min(100, $this->getLevel() * 0.10);
	}

	public function getGracefulRollChance() : float{
		return min(100, $this->getLevel() * 0.20);
	}

	public function getDodgeChance() : float{
		return min(20, $this->getLevel() * 0.025);
	}
}