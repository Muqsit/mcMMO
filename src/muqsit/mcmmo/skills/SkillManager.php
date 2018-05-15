<?php
namespace muqsit\mcmmo\skills;

use muqsit\mcmmo\skills\excavation\ExcavationSkill;
use muqsit\mcmmo\sounds\McMMOLevelUpSound;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SkillManager{

	/** @var string[] */
	private static $skills = [];

	/** @var Skill[] */
	private $skill_tree = [];

	/** @var Player */
	private $player;

	public static function registerSkill(string $class, bool $override = false) : void{
		$skillId = $class::SKILL_ID;
		if($skillId < 0){
			throw new \Error("SkillId cannot be negative, got $skillId");
		}

		if(!isset(SkillManager::$skills[$skillId]) || $override){
			if(isset(SkillManager::$skills[$skillId])){
				$oldskill = SkillManager::$skills[$skillId];
				$oldlistener = $oldSkill::getListenerClass();
				if($oldlistener !== null && is_subclass_of($oldskill, SkillListener::class, true)){
					//TODO: Unregister oldlistener
				}
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
			return;
		}

		throw new \Error("Attempted to override skill " . SkillManager::$skills[$skillId]);
	}

	public static function registerDefaults() : void{
		SkillManager::registerSkill(ExcavationSkill::class);
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

	public function __construct(Player $player, array $skill_tree){
		$this->player = $player;
		$this->setSkillTree($skill_tree);
	}

	public function getPlayer() : Player{
		return $this->player;
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

	private function setSkillTree(array $skill_tree) : void{
		foreach($skill_tree as $skillId => $skillInfo){
			$this->skill_tree[$skillId] = SkillManager::getSkillInstance($skillId, $skillInfo); 
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

	public function getSkillXp(int $skillId) : int{
		return $this->getSkill($skillId)->getXp();
	}

	public function getSkillLevel(int $skillId) : int{
		return $this->getSkill($skillId)->getLevel();
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

	public function merge(SkillManager $skill) : void{
		foreach($skill->getSkillTree() as $skillId => $instance){
			$this->addXp($skillId, $instance->getXp());
		}
	}
}