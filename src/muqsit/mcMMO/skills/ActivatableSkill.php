<?php
namespace muqsit\mcMMO\skills;

use muqsit\mcMMO\handlers\HandlerManager;
use muqsit\mcMMO\mcMMO;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class ActivatableSkill extends BaseSkill{

    /** @var int|null */
    private $activationEnd;

    /** @var int */
    private $cooldown = 0;

    protected function getHandlerManager() : HandlerManager{
        return mcMMO::getInstance()->getHandlerManager();
    }

    /**
     * Checks whether the activation time
     * has expired.
     *
     * Returns true if the activation time
     * has not expired.
     *
     * @return bool
     */
    public function isActivationOngoing() : bool{
        return $this->activationEnd !== null && ($this->activationEnd - time()) >= 0;
    }

    /**
     * Activates the item.
     *
     * @param int $expiration
     * @param int $cooldown
     */
    public function activate(int $expiration = 2, int $cooldown = 120){
        $this->activationEnd = time() + $expiration;
        $this->cooldown = time() + $cooldown;
    }

    /**
     * Returns the skill cooldown.
     * If there is no cooldown, it
     * returns a negative integer.
     *
     * @return int
     */
    public function getCooldown() : int{
        return $this->cooldown - time();
    }

    /**
     * Checks whether the skill can be
     * activated.
     *
     * @param null $error
     *
     * @return bool
     */
    public function canActivate(&$error = null) : bool{
        if($this->isActivationOngoing()){
            return false;
        }

        $cooldown = $this->getCooldown();
        if($cooldown > 0){
            $error = TextFormat::RED."You are too tired to use that ability again. ".TextFormat::YELLOW."({$cooldown}s)";
            return false;
        }
        return true;
    }

    /**
     * Handles skill activation and
     * activates the skill if the criteria
     * is met.
     *
     * @parma null $error
     *
     * @return bool
     */
    public function handleActivation(Player $player, &$error = null) : bool{
        if($this->canActivate($error)){
            $this->activate();
            return true;
        }
        return false;
    }

    /**
     * Sends a delayed message to the
     * player.
     *
     * @param string $message
     * @param int $delay
     */
    public function sendDelayedMessage(Player $player, string $message, int $delay = 2){
        $player->getServer()->getScheduler()->scheduleDelayedTask(new class($player, $message) extends Task{

            /** @var Player */
            private $player;

            /** @var string */
            private $message;

            public function __construct(Player $player, string $message){
                $this->player = $player;
                $this->message = $message;
            }

            public function onRun(int $tick){
                if($this->player->isAlive()){
                    $this->player->sendMessage($this->message);
                }
            }
        }, 20 * $delay);
    }
}