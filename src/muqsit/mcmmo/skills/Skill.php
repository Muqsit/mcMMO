<?php
namespace muqsit\mcmmo\skills;

abstract class Skill implements SkillIds{

	const SKILL_ID = -1;

	/**
	 * The Listener class to register upon
	 * registering this skill in SkillManager.
	 *
	 * @return string|null;
	 */
	public static function getListenerClass() : ?string{
		return null;
	}

	/** @var int */
	protected $xp;

	/** @var int */
	protected $level;

	public function __construct(array $args = []){
		$this->xp = $args["xp"] ?? 0;
		$this->level = $args["level"] ?? 0;
	}

	/**
	 * This skill's xp.
	 *
	 * @return int
	 */
	public function getXp() : int{
		return $this->xp;
	}

	/**
	 * This skill's level.
	 *
	 * @return int
	 */
	public function getLevel() : int{
		return $this->level;
	}

	/**
	 * This level's maximum xp value.
	 *
	 * @return int
	 */
	public function getMaxLevelXp() : int{
		return 1000 + ($this->level + 1) * $this->getXpIncreasePerLevel();
	}

	/**
	 * XP increment factor by level.
	 *
	 * @return int
	 */
	public function getXpIncreasePerLevel() : int{
		return 20;
	}

	/**
	 * Adds xp to this skill.
	 *
	 * @param int $xp
	 * @return bool whether levelled up
	 */
	public function addXp(int $xp, &$increase = null) : bool{
		$increase = 0;
		$remainingXp = $xp + $this->xp;

		while($remainingXp > ($max = $this->getMaxLevelXp())){
			++$increase;
			$remainingXp -= $max;
		}

		if($increase){
			$this->xp = $remainingXp;
		}else{
			$this->xp += $xp;
		}

		$this->level += $increase;
		return $increase > 0;
	}

	/**
	 * This is stored in the savedata
	 * and fetched in the constructor.
	 *
	 * @return array
	 */
	public function serialize() : array{
		return [
			"xp" => $this->xp,
			"level" => $this->level
		];
	}

	/**
	 * The name of this skill.
	 * The name DOESN'T get stored as
	 * an index in savedata.
	 *
	 * @return string
	 */
	abstract public function getName() : string;

	/**
	 * A short description about how players
	 * can gain xp for this skill.
	 *
	 * @return string
	 */
	abstract public function getShortDescription() : string;
}