<?php
namespace muqsit\mcmmo\skills\excavation;

use muqsit\mcmmo\skills\Skill;

class ExcavationSkill extends Skill{

	const SKILL_ID = self::EXCAVATION;

	public static function getListenerClass() : ?string{
		return ExcavationListener::class;
	}

	public function getName() : string{
		return "Excavation";
	}

	public function getShortDescription() : string{
		return "Digging and finding treasures";
	}
}