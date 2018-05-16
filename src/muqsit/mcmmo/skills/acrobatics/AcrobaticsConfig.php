<?php
namespace muqsit\mcmmo\skills\acrobatics;

class AcrobaticsConfig{

	const TYPE_ROLL = 0;
	const TYPE_GRACEFUL_ROLL = 1;
	const TYPE_DODGE = 2;

	const XP_MAP = [
		AcrobaticsConfig::TYPE_ROLL => 80,
		AcrobaticsConfig::TYPE_GRACEFUL_ROLL => 80,
		AcrobaticsConfig::TYPE_DODGE => 120
	];

	public function getXpReward(int $type, float $damage) : int{
		return AcrobaticsConfig::XP_MAP[$type] * floor($damage / 0.5);
	}

	public function getRollDamageThreshold(bool $graceful) : float{
		return $graceful ? 14.0 : 7.0;
	}

	public function getDodgeDamageModifier() : float{
		return 2.0;
	}
}