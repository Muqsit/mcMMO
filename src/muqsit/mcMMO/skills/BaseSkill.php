<?php
namespace muqsit\mcMMO\skills;

use pocketmine\Player;

class BaseSkill{

    const XP_GAIN_THRESHOLD = PHP_INT_MAX;//limits the amount of experience a player can earn per XP_GAIN_THRESHOLD_INTERVAL
    const XP_GAIN_THRESHOLD_INTERVAL = 10;//minutes

    /** @var int */
    protected $xp;

    /** @var int */
    private $lastXpGainMinute = PHP_INT_MIN;

    /** @var int */
    private $xpGainedLastMinute = 0;

    /**
     * Increases this skill's experience.
     *
     * @param Player $player
     * @param int $xp
     */
    public function applyXpGain(Player $player, int $xp){
        if(!$this->thresholdReached()){
            $this->updateThreshold($xp);
            $this->xp += $xp;
        }
    }

    /**
     * Returns whether the experience
     * threshold has been reached.
     *
     * @return bool
     */
    public function thresholdReached() : bool{
        return $this->xpGainedLastMinute >= static::XP_GAIN_THRESHOLD;
    }

    /**
     * Updates the experience threshold.
     *
     * @param int $xp
     */
    private function updateThreshold(int $xp){
        if(static::XP_GAIN_THRESHOLD !== PHP_INT_MAX && static::XP_GAIN_THRESHOLD_INTERVAL !== PHP_INT_MAX){
            $minute = date("i");
            if($minute - $this->lastXpGainMinute >= static::XP_GAIN_THRESHOLD_INTERVAL){
                $this->xpGainedLastMinute = 0;
                $this->lastXpGainMinute = $minute;
            }else{
                $this->xpGainedLastMinute += $xp;
            }
        }
    }

    /**
     * Returns this skill's experience.
     *
     * @return int
     */
    public function getXp() : int{
        return $this->xp;
    }

    /**
     * Returns arguments for recreating
     * this class instance.
     */
    public function getData(){
        return $this->xp;
    }
}