<?php
namespace muqsit\mcmmo\database;

use muqsit\mcmmo\skills\SkillManager;
use muqsit\mcmmo\skills\TemporarySkillManager;

use pocketmine\Player;
use pocketmine\Server;

abstract class Database{

	const STATE_LOADING = 0;

	const DATABASES = [
		IgBinaryDatabase::class => ["igbinary"],
		JSONDatabase::class => ["json"],
		YAMLDatabase::class => ["yml", "yaml"]
	];

	public static function getFromString(string $string, ...$args) : ?Database{
		$string = strtolower($string);
		foreach(self::DATABASES as $class => $databases){
			if(in_array($string, $databases)){
				return new $class(...$args);
			}
		}

		return null;
	}

	/** @var Server */
	protected $server;

	/** @var SkillManager[] */
	protected $loaded = [];

	/** @var int[] */
	private $state = [];

	public function __construct(){
		$this->server = Server::getInstance();
	}

	/**
	 * Returns the state of player data.
	 *
	 * @param Player $player
	 * @return int|null if loaded
	 */
	public function getState(Player $player) : ?int{
		return $this->state[$player->getId()] ?? null;
	}

	/**
	 * @param Player $player
	 * @param int|null $state
	 */
	final public function setState(Player $player, ?int $state) : void{
		$this->setStateByPlayerId($player->getId(), $state);
	}

	/**
	 * Sets the state of a player or unsets if
	 * state is null.
	 *
	 * @param Player $player
	 * @param int|null $state
	 */
	final public function setStateByPlayerId(int $playerId, ?int $state) : void{
		if($state === null){
			unset($this->state[$playerId]);
			return;
		}

		$this->state[$playerId] = $state;
	}

	/**
	 * @param Player $player
	 * @return bool whether the player is loading
	 */
	final public function isLoading(Player $player) : bool{
		return $this->getState($player) === Database::STATE_LOADING;
	}

	/**
	 * Loads the player's information from
	 * database to cache.
	 *
	 * @param Player $player
	 */
	final public function load(Player $player) : void{
		$this->setState($player, Database::STATE_LOADING);
		$this->loadFromDatabase($player);
	}

	/**
	 * Called when player's information from
	 * the database has been received.
	 * This won't get called if the player's state
	 * is not set to Database::STATE_LOADING.
	 *
	 * @param Player $player
	 */
	final public function onLoad(Player $player, array $loaded = []) : void{
		$this->setState($player, null);

		$manager = new SkillManager($player, $loaded);
		if(isset($this->loaded[$player = $player->getLowerCaseName()])){
			$manager->merge($this->loaded[$player]);
		}

		$this->loaded[$player] = $manager;
	}

	/**
	 * Returns player's array of skills in the form
	 * of SkillManager.
	 * TemporarySkillManager is returned if the
	 * player is still loading.
	 *
	 * @param Player $player
	 * @return SkillManager
	 */
	final public function getLoaded(Player $player) : SkillManager{
		return $this->loaded[$player = $player->getLowerCaseName()] ?? ($this->loaded[$player] = new TemporarySkillManager($player));
	}

	/**
	 * Saves the player's cached information to
	 * database.
	 *
	 * @param Player $player
	 * @param bool $removeCached
	 */
	final public function save(Player $player, bool $removeCached = false) : void{
		if(isset($this->loaded[$playerId = $player->getLowerCaseName()]) && !($this->loaded[$playerId] instanceof TemporarySkillManager)){
			$this->saveToDatabase($player->getLowerCaseName(), $this->loaded[$playerId]->getSkillTree(true));
			if($removeCached){
				unset($this->loaded[$playerId]);
			}
		}

		$this->setState($player, null);
	}

	/**
	 * Saves all cached / loaded player data.
	 */
	final public function saveAll() : void{
		foreach($this->loaded as $player => $loaded){
			$this->saveToDatabase($player, $loaded->getSkillTree(true));
		}
	}

	/**
	 * Called when the plugin disables, so
	 * databases like SQLite can close
	 * themselves.
	 */
	public function onClose() : void{
	}

	/**
	 * Called during Database::load() to later
	 * call Database::onLoad().
	 *
	 * @param Player $player
	 */
	abstract protected function loadFromDatabase(Player $player) : void;

	/**
	 * Called during Database::save() to save
	 * loaded (cached) information of player.
	 *
	 * @param Player $player
	 * @param array $loaded
	 */
	abstract protected function saveToDatabase(string $player, array $loaded) : void;
}