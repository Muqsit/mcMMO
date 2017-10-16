<?php
namespace muqsit\mcMMO\commands;

use muqsit\mcMMO\mcMMO;

use pocketmine\command\{CommandSender, PluginCommand};
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class mcMMOCommand extends PluginCommand{

    public static function registerCommands(mcMMO $plugin){
        $commands = [];
        foreach([
            "woodcutting" => [WoodcuttingCommand::class]
        ] as $cmd => $data){
            $commands[$cmd] = new $data[0]($cmd, $plugin);
            if (isset($data[1]) && !empty($data[1])) {
                $commands[$cmd]->setAliases($data[1]);
            }
            if (isset($data[2]) && $data[2] !== "") {
                $commands[$cmd]->setPermission($data[2]);
            }
        }
        $plugin->getServer()->getCommandMap()->registerAll("mcMMO", $commands);
    }

    public function __construct($command, mcMMO $plugin){
        parent::__construct($command, $plugin);
    }

    protected function generateLineBreak(string $title) : string{
        return TextFormat::RED.str_repeat("-", 5)."[]".TextFormat::GREEN.$title.TextFormat::RED."[]".str_repeat("-", 5);
    }

    public function testPermission(CommandSender $target) : bool{
        if(!$this->canConsoleExecute() && !($target instanceof Player)){
            $target->sendMessage("This command does not support console usage.");
            return false;
        }
        return parent::testPermission($target);
    }

    protected function canConsoleExecute() : bool{
        return true;
    }
}