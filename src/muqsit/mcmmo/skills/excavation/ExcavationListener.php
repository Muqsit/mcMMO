<?php
namespace muqsit\mcmmo\skills\excavation;

use muqsit\mcmmo\skills\SkillListener;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;

class ExcavationListener extends SkillListener{

	/** @var ExcavationConfig */
	private $config;

	protected function init() : void{
		$this->config = new ExcavationConfig();
	}

	/**
	 * @param BlockBreakEvent
	 * @priority HIGH
	 * @ignoreCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		$player = $event->getPlayer();
		$skills = $this->plugin->getSkillManager($player);
		$event->setDrops($this->config->getDrops($player, $event->getItem(), $event->getBlock(), $skills->getSkillLevel(self::EXCAVATION), $xpreward));

		if($xpreward > 0){
			$skills->addSkillXp(self::EXCAVATION, $xpreward);
		}
	}
}