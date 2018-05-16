<?php
namespace muqsit\mcmmo;

use muqsit\mcmmo\commands\McMMOCommand;
use muqsit\mcmmo\commands\SkillCommand;
use muqsit\mcmmo\database\Database;
use muqsit\mcmmo\skills\SkillManager;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase{

	/** @var Database */
	private $database;

	public function onEnable() : void{
		$this->saveResource("database.yml");
		$this->saveResource("help.ini");

		McMMOCommand::registerDefaults($this);
		SkillCommand::loadHelpPages($this->getDataFolder() . "help.ini");
		SkillManager::registerDefaults();

		$database = new Config($this->getDataFolder() . "database.yml");
		$dbtype = $database->get("type");
		$args = $database->get(strtoupper($dbtype));

		$this->database = Database::getFromString($dbtype, $this->getDataFolder() . $args["datafolder"] . DIRECTORY_SEPARATOR);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function onDisable() : void{
		$this->getDatabase()->saveAll();
		$this->getDatabase()->onClose();
	}

	public function getDatabase() : Database{
		return $this->database;
	}

	public function getSkillManager(Player $player) : SkillManager{
		return $this->database->getLoaded($player);
	}
}