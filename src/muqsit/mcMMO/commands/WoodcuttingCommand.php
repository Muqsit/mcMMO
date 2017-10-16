<?php
namespace muqsit\mcMMO\commands;

use muqsit\mcMMO\skills\Woodcutting;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class WoodcuttingCommand extends mcMMOCommand{

    const GUIDE = [
        TextFormat::DARK_AQUA."About woodcutting:",
        TextFormat::YELLOW."Woodcutting is all about chopping down trees.",
        " ",
        TextFormat::DARK_AQUA."XP GAIN: ",
        TextFormat::YELLOW."XP is gained whenever you break log blocks.",
        " ",
        " ",
        " ",

        TextFormat::DARK_AQUA."Hoe does Tree Feller work?",
        TextFormat::YELLOW."Tree Feller is an active ability, you can right-click",
        TextFormat::YELLOW."while holding an axe to activate Tree Feller. This will",
        TextFormat::YELLOW."cause the entire tree to break instantly, dropping all",
        TextFormat::YELLOW."it's logs at once. Pocket Edition players can long-tap",
        TextFormat::YELLOW."the axe and mine logs to activate Tree Feller.",
        " ",
        " ",

        TextFormat::DARK_AQUA."How does Leaf Blower work?",
        TextFormat::YELLOW."Leaf Blower is a passive ability that will cause leaf",
        TextFormat::YELLOW."blocks to break instantly when hit with an axe. By default,",
        TextFormat::YELLOW."this ability unlocks at level 100.",
        " ",
        " ",
        " ",
        " ",

        TextFormat::DARK_AQUA."How do Double Drops work?",
        TextFormat::YELLOW."This passive ability gives you a chance to obtain an extra",
        TextFormat::YELLOW."block for every log you chop.",
        " ",
        " ",
        " ",
        " ",
        " ",
    ];

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }

        $skill = $this->getPlugin()->getPlayer($sender->getId())->getSkillManager()->getWoodcutting();

        if(!isset($args[0])){
            $sender->sendMessage(implode("\n", [
                $this->generateLineBreak("Woodcutting"),
                TextFormat::DARK_GRAY."XP GAIN: ".TextFormat::WHITE."Chopping down trees",
                TextFormat::DARK_GRAY."LVL: ".TextFormat::GREEN.$skill->getLevel().TextFormat::DARK_AQUA." XP".TextFormat::YELLOW."(".TextFormat::GOLD.number_format($skill->getXp()).TextFormat::YELLOW."/".TextFormat::GOLD.number_format($skill->getNextLevelXp()).TextFormat::YELLOW.")",
                $this->generateLineBreak("EFFECTS"),
                TextFormat::DARK_AQUA."Tree Feller (ABILITY): ".TextFormat::GREEN."Make trees explode",
                TextFormat::DARK_AQUA."Leaf Blower: ".TextFormat::GREEN."Blow Away Leaves",
                TextFormat::DARK_AQUA."Double Drops: ".TextFormat::GREEN."Double the normal loot",
                $this->generateLineBreak("YOUR STATS"),
                $skill->getLevel() >= Woodcutting::LEAF_BLOWER_LVL_REQUIREMENT ? TextFormat::RED."Leaf Blower: ".TextFormat::YELLOW."Blow away leaves" : TextFormat::GRAY."LOCKED UNTIL 100+ SKILL (LEAF BLOWER)",
                TextFormat::RED."Double Drop Chance: ".TextFormat::YELLOW.sprintf("%0.2f", $skill->getDoubleDropRate())."%",
                TextFormat::RED."Tree Feller Length: ".TextFormat::YELLOW.$skill->getTreeFellerDuration(),
                TextFormat::DARK_AQUA."Guide for Woodcutting available - type /woodcutting ? [page]"
            ]));
        }else{
            if($args[0] !== "?"){
                $sender->sendMessage(TextFormat::RED."Proper usage is /woodcutting ? [page]");
            }else{
                $page = $args[1] ?? 1;
                $pages = array_chunk(self::GUIDE, 8);
                if(!is_numeric($page)){
                    $sender->sendMessage("Not a valid page number!");
                    return false;
                }
                if($page < 1 || $page > count($pages)){
                    $sender->sendMessage("That page does not exist, there are only ".count($pages)." total pages.");
                    return false;
                }
                $message = $pages[--$page];
                $message[] = TextFormat::WHITE."Page ".++$page." of ".count($pages);
                array_unshift($message, TextFormat::GOLD."-=".TextFormat::GREEN."Woodcutting Guide".TextFormat::GOLD."=-");
                $sender->sendMessage(implode("\n", $message));
            }
        }
        return true;
    }

    protected function canConsoleExecute() : bool{
        return false;
    }
}