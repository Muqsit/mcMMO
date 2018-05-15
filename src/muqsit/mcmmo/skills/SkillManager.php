<?php
namespace muqsit\mcmmo\skills;

use muqsit\mcmmo\skills\excavation\ExcavationSkill;
use muqsit\mcmmo\skills\tasks\AbilityCooldownNotifyTask;
use muqsit\mcmmo\skills\tasks\AbilityDeactivateNotifyTask;
use muqsit\mcmmo\skills\woodcutting\WoodcuttingSkill;
use muqsit\mcmmo\sounds\McMMOLevelUpSound;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SkillManager{

	const TASK_ABILITY_DEACTIVATE_NOTIFY = 0;
	const TASK_ABILITY_COOLDOWN_NOTIFY = 1;

	/** @var string[] */
	private static $skills = [];

	/** @var string[] */
	private static $skill_names = [];

	/** @var int[] */
	private static $skill_identifiers = [];

	/** @var Skill[] */
	private $skill_tree = [];

	/** @var Player */
	private $player;

	/** @var int[] */
	private $taskIds = [];

	/** @var bool */
	private $can_use_abilities;

	public static function registerSkill(string $class, bool $override = false) : void{
		$skillId = $class::SKILL_ID;
		if($skillId < 0){
			throw new \Error("SkillId cannot be negative, got $skillId");
		}

		if(!isset(SkillManager::$skills[$skillId]) || $override){
			if(isset(SkillManager::$skills[$skillId])){
				$oldskill = SkillManager::$skills[$skillId];
				$oldlistener = $oldskill::getListenerClass();
				if($oldlistener !== null && is_subclass_of($oldskill, SkillListener::class, true)){
					//TODO: Unregister oldlistener
				}

				SkillManager::removeSkillIdentifiers($skillId);
				unset(SkillManager::$skill_names[strtolower((new $oldskill())->getName())]);
			}

			SkillManager::$skills[$skillId] = $class;

			$listener = $class::getListenerClass();
			if($listener !== null){
				if (!is_subclass_of($listener, SkillListener::class, true)) {
					throw new \Error("$listener must be an instance of " . SkillListener::class);
				}

				$server = Server::getInstance();
				$plugin = $server->getPluginManager()->getPlugin("mcMMO");
				$server->getPluginManager()->registerEvents(new $listener($plugin), $plugin);
			}

			$identifiers = $class::getItemIdentifies();
			if($identifiers !== null){
				SkillManager::addSkillIdentifiers($skillId, ...$identifiers);
			}

			SkillManager::$skill_names[strtolower((new $class())->getName())] = $skillId;
			return;
		}

		throw new \Error("Attempted to override skill " . SkillManager::$skills[$skillId]);
	}

	public static function registerDefaults() : void{
		SkillManager::registerSkill(ExcavationSkill::class);
		SkillManager::registerSkill(WoodcuttingSkill::class);
	}

	public static function addSkillIdentifiers(int $skillId, int ...$itemIds) : void{
		foreach($itemIds as $itemId){
			SkillManager::$skill_identifiers[$itemId] = $skillId;
		}
	}

	public static function removeSkillIdentifiers(int $skillId) : void{
		foreach(array_keys(SkillManager::$skill_identifiers, $skillId, true) as $itemId){
			unset(SkillManager::$skill_identifiers[$itemId]);
		}
	}

	public static function getSkillIdByName(string $name) : ?int{
		return SkillManager::$skill_names[strtolower($name)] ?? null;
	}

	public static function getSkillClass(int $skillId) : ?string{
		return SkillManager::$skills[$skillId] ?? null;
	}

	private static function getSkillInstance(int $skillId, ...$args) : ?Skill{
		if(isset(SkillManager::$skills[$skillId])){
			$class = SkillManager::$skills[$skillId];
			return new $class(...$args);
		}

		return null;
	}

	public function __construct(Player $player, array $savedata){
		$this->player = $player;
		$this->setCanUseAbilities($savedata["ability_use"] ?? true);
		$this->setSkillTree($savedata["skill_tree"] ?? []);
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function canUseAbilities() : bool{
		return $this->can_use_abilities;
	}

	public function setCanUseAbilities(bool $value = true) : void{
		$this->can_use_abilities = $value;
	}

	public function getSkillTree(bool $clean = false) : array{
		if($clean){
			$skills = $this->getSkillTree();
			foreach($skills as &$skill){
				$skill = $skill->serialize();
			}

			return $skills;
		}

		return $this->skill_tree;
	}

	public function toSaveData() : array{
		return [
			"ability_use" => $this->canUseAbilities(),
			"skill_tree" => $this->getSkillTree(true)
		];
	}

	private function setSkillTree(array $skill_tree) : void{
		foreach($skill_tree as $skillId => $skillInfo){
			$this->skill_tree[$skillId] = $skill = SkillManager::getSkillInstance($skillId, $skillInfo);
			$this->scheduleSkillTasks($skill);
		}
	}

	public function addSkillXp(int $skillId, int $xp) : void{
		$skill = $this->getSkill($skillId);
		if($skill->addXp($xp, $increase)){
			$player = $this->getPlayer();
			$player->sendMessage(TextFormat::YELLOW . $skill->getName() . " skill increased by $increase. Total (" . $skill->getLevel() . ")");
			$player->getLevel()->addSound(new McMMOLevelUpSound($player->x, $player->y, $player->z), [$player]);
		}
	}

	public function activateAbility(int $skillId) : bool{
		$skill = $this->getSkill($skillId);
		$player = $this->getPlayer();
		if($skill->activateAbility($player)){
			$this->scheduleSkillTasks($skill);
			return true;
		}
		return false;
	}

	public function getSkill(int $skillId) : ?Skill{
		if(isset($this->skill_tree[$skillId])){
			return $this->skill_tree[$skillId];
		}

		$skill = SkillManager::getSkillInstance($skillId);
		if($skill !== null){
			return $this->skill_tree[$skillId] = $skill;
		}

		return null;
	}

	public function getSkillByItem(Item $item) : ?Skill{
		if(isset(SkillManager::$skill_identifiers[$itemId = $item->getId()])){
			return $this->getSkill(SkillManager::$skill_identifiers[$itemId]);
		}

		return null;
	}

	public function merge(SkillManager $skill) : void{
		foreach($skill->getSkillTree() as $skillId => $instance){
			$this->addXp($skillId, $instance->getXp());
		}
	}

	public function scheduleSkillTasks(Skill $skill) : void{
		$scheduler = $this->getPlayer()->getServer()->getScheduler();
		$skillId = $skill->getId();

		if(isset($this->taskIds[SkillManager::TASK_ABILITY_DEACTIVATE_NOTIFY][$skillId])){
			$scheduler->cancelTask($this->taskIds[SkillManager::TASK_ABILITY_DEACTIVATE_NOTIFY][$skillId]);
		}

		$expire = $skill->getAbilityExpire();
		if($expire > 0){
			$scheduler->scheduleDelayedTask($task = new AbilityDeactivateNotifyTask($this, $skillId), $expire * 20);
			$this->addIncompleteTask(SkillManager::TASK_ABILITY_DEACTIVATE_NOTIFY, $skillId, $task->getTaskId());
		}

		if(isset($this->taskIds[SkillManager::TASK_ABILITY_COOLDOWN_NOTIFY][$skillId])){
			$scheduler->cancelTask($this->taskIds[SkillManager::TASK_ABILITY_COOLDOWN_NOTIFY][$skillId]);
		}

		$expire = $skill->getAbilityCooldownExpire();
		if($expire > 0){
			$scheduler->scheduleDelayedTask($task = new AbilityCooldownNotifyTask($this, $skillId), $expire * 20);
			$this->addIncompleteTask(SkillManager::TASK_ABILITY_COOLDOWN_NOTIFY, $skillId, $task->getTaskId());
		}
	}

	public function addIncompleteTask(int $id, int $skillId, int $taskId) : void{
		$this->taskIds[$id][$skillId] = $taskId;
	}

	public function setTaskAsCompleted(int $id, int $skillId) : void{
		unset($this->taskIds[$id][$skillId]);
	}

	public function close(Server $server) : void{
		$scheduler = $server->getScheduler();
		foreach($this->taskIds as $taskIds){
			foreach($taskIds as $taskId){
				$scheduler->cancelTask($taskId);
			}
		}

		$this->taskIds = [];
	}
}