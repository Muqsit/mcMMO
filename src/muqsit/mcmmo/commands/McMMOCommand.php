<?php
namespace muqsit\mcmmo\commands;

use muqsit\mcmmo\Loader;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;

abstract class McMMOCommand extends PluginCommand implements CommandExecutor{

	const FLAG_NO_CONSOLE = 0b10000000000000;

	public static function registerDefaults(Loader $plugin) : void{
		$commands = [];

		foreach([
			//Admin commands
			"addlevels" =>   [AddLevelsCommand::class, [], "mcmmo.commands.modifs"],
			"addxp" =>       [AddXpCommand::class, [], "mcmmo.commands.modifs"],
			"mcrefresh" =>   [McRefreshCommand::class, [], "mcmmo.commands.modifs"],
			"skillreset" =>  [SkillResetCommand::class, [], "mcmmo.commands.modifs"],

			//Skill commands
			"excavation" =>  [ExcavationCommand::class, [], "mcmmo.commands.skills"],
			"woodcutting" => [WoodcuttingCommand::class, [], "mcmmo.commands.skills"],

			//Other commands
			"mcability" =>   [McAbilityCommand::class, [], "mcmmo.commands.others"]
		] as $cmd => $data){
			$commands[$cmd] = new $data[0]($cmd, $plugin);

			if(!empty($data[1])){
				$commands[$cmd]->setAliases($data[1]);
			}

			if(!empty($data[2])){
				$commands[$cmd]->setPermission($data[2]);
			}

			if(!empty($data[3])){
				$commands[$cmd]->setDescription($data[3]);
			}
		}

		$plugin->getServer()->getCommandMap()->registerAll($plugin->getName(), $commands);
	}

	/** @var int */
	private $flags;

	public function __construct(string $cmd, Loader $loader){
		parent::__construct($cmd, $loader);
		$this->setExecutor($this);
		$this->init();
	}

	public function setFlag(int $flag) : void{
		if(!$this->isFlagSet($flag)){
			$this->flags |= $flag;
		}
	}

	public function removeFlag(int $flag) : void{
		if($this->isFlagSet($flag)){
			$this->flags &= ~$flag;
		}
	}

	public function isFlagSet(int $flag) : bool{
		return ($this->flags & $flag) === $flag;
	}

	public function testFlags(CommandSender $sender) : bool{
		if($sender instanceof ConsoleCommandSender && $this->isFlagSet(McMMOCommand::FLAG_NO_CONSOLE)){
			$sender->sendMessage(TextFormat::RED . "This command does not support console usage.");
			return false;
		}

		return true;
	}

	final public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testFlags($sender)){
			return false;
		}

		return parent::execute($sender, $commandLabel, $args);
	}

	protected function init() : void{
	}
}