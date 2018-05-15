<?php
namespace muqsit\mcmmo\database;

use muqsit\mcmmo\database\tasks\FlatFileReadTask;

use pocketmine\Player;
use pocketmine\scheduler\FileWriteTask;

abstract class FlatFileDatabase extends Database{

	const TYPE = -1;
	const TYPE_YAML = 0;
	const TYPE_JSON = 1;
	const TYPE_IGBINARY = 2;

	const EXTENSIONS = [
		FlatFileDatabase::TYPE_YAML =>     ".yml",
		FlatFileDatabase::TYPE_JSON =>     ".json",
		FlatFileDatabase::TYPE_IGBINARY => ".igbinary"
	];

	/** @var string */
	protected $path;

	public function __construct(string $path){
		parent::__construct();

		if(!is_dir($path)){
			mkdir($path);
		}

		$this->path = $path;
	}

	protected function loadFromDatabase(Player $player) : void{
		$path = $this->path . $player->getLowerCaseName() . static::EXTENSIONS[static::TYPE];
		if(!file_exists($path)){
			$this->onLoad($player);
			return;
		}

		$task = new FlatFileReadTask($player, $path, static::TYPE);
		$this->server->getScheduler()->scheduleAsyncTask($task);
	}

	protected function saveToDatabase(string $player, array $loaded) : void{
		$path = $this->path . $player . static::EXTENSIONS[static::TYPE];
		if(empty($loaded)){
			if(file_exists($path)){
				unlink($path);
			}
			return;
		}

		$this->writeToFile($path, $this->serialize($loaded));
	}

	protected function writeToFile(string $file, string $data) : void{
		$this->server->getScheduler()->scheduleAsyncTask(new FileWriteTask($file, $data));
	}

	abstract protected function serialize(array $loaded) : string;
}