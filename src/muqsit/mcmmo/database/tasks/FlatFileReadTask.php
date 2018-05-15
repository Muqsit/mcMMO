<?php
namespace muqsit\mcmmo\database\tasks;

use muqsit\mcmmo\database\FlatFileDatabase;

use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class FlatFileReadTask extends AsyncTask{

	/** @var string */
	private $playerRawUUID;

	/** @var int */
	private $playerId;

	/** @var string */
	private $file;

	/** @var int */
	private $type;

	public function __construct(Player $player, string $file, int $type){
		$this->playerRawUUID = $player->getRawUniqueId();
		$this->playerId = $player->getId();

		$this->file = $file;
		$this->type = $type;
	}

	public function onRun() : void{
		switch($this->type){
			case FlatFileDatabase::TYPE_JSON:
				$result = json_decode(file_get_contents($this->file), true);
				break;
			case FlatFileDatabase::TYPE_YAML:
				$result = yaml_parse_file($this->file);
				break;
			case FlatFileDatabase::TYPE_IGBINARY:
				$result = igbinary_unserialize(file_get_contents($this->file));
				break;
		}

		$this->setResult($result ?? []);
	}

	public function onCompletion(Server $server) : void{
		$player = $server->getPlayerByRawUUID($this->playerRawUUID);
		$database = $server->getPluginManager()->getPlugin("mcMMO")->getDatabase();

		if($player === null){
			$database->setStateByPlayerId($this->playerId, null);
		}elseif($database->isLoading($player)){
			$database->onLoad($player, $this->getResult());
		}
	}
}