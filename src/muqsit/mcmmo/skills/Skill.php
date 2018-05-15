<?php
namespace muqsit\mcmmo\skills;

use pocketmine\Player;

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

	/**
	 * Returns all item ids that identify this ability.
	 *
	 * @return int[]
	 */
	public static function getItemIdentifies() : ?array{
		return null;
	}

	/** @var int */
	protected $xp;

	/** @var int */
	protected $level;

	/** @var int */
	protected $ability_expire;

	/** @var int */
	protected $ability_cooldown_expire;

	public function __construct(array $args = []){
		$this->xp = $args["xp"] ?? 0;
		$this->level = $args["level"] ?? 0;
		$this->ability_expire = $args["ability_expire"] ?? 0;
		$this->ability_cooldown_expire = $args["ability_cooldown_expire"] ?? 0;
	}

	/**
	 * Returns this skill's identifier.
	 *
	 * @return int
	 */
	public function getId() : int{
		return static::SKILL_ID;
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
			"level" => $this->level,
			"ability_expire" => $this->ability_expire,
			"ability_cooldown_expire" => $this->ability_cooldown_expire
		];
	}

	/**
	 * Returns whether the ability is activated
	 * (generally done by right-clicking a tool).
	 *
	 * @return bool
	 */
	public function hasAbility() : bool{
		return $this->ability_expire >= time();
	}

	/**
	 * Activates ability for duration seconds.
	 * @return bool whether the ability is activated.
	 */
	final public function activateAbility(Player $player) : bool{
		if($this->isAbilityOnCooldown()){
			return false;
		}

		$this->ability_expire = time() + $this->getAbilityDuration();
		$this->setAbilityCooldown();
		$this->onActivateAbility($player);
		return true;
	}

	/**
	 * Returns the duration for a given ability.
	 *
	 * @return int
	 */
	public function getAbilityDuration() : int{
		return 2 * floor($this->getLevel() / 50) + 2;
	}

	/**
	 * Returns whether the ability is on
	 * cooldown.
	 *
	 * @return bool
	 */
	public function isAbilityOnCooldown() : bool{
		return $this->ability_cooldown_expire > time();
	}

	/**
	 * Returns the cooldown for a given ability.
	 *
	 * @return int
	 */
	public function getAbilityCooldown() : int{
		return 250;
	}

	/**
	 * Returns the time left for the ability to
	 * deactivate (not to be confused with
	 * ability's cooldown).
	 *
	 * @return int
	 */
	public function getAbilityExpire() : int{
		return max(0, $this->ability_expire - time());
	}

	/**
	 * Returns the time left for the abiity's cooldown
	 * to expire.
	 *
	 * @return int
	 */
	public function getAbilityCooldownExpire() : int{
		return max(0, $this->ability_cooldown_expire - time());
	}

	/**
	 * Sets cooldown for an ability.
	 */
	public function setAbilityCooldown() : void{
		$this->ability_cooldown_expire = time() + $this->getAbilityCooldown();
	}

	/**
	 * Called when player activates the ability.
	 */
	public function onActivateAbility(Player $player) : void{
	}

	/**
	 * Returns the ability name. This is show when
	 * the ability gets activated.
	 *
	 * @return string
	 */
	public function getAbilityName() : string{
		return $this->getName();
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