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
		$manager = $this->plugin->getSkillManager($player);
		$skill = $manager->getSkill(self::EXCAVATION);
		$event->setDrops($this->config->getDrops($player, $event->getItem(), $event->getBlock(), $skill->getLevel(), $skill->hasAbility(), $xpreward));

		if($xpreward > 0){
			$manager->addSkillXp(self::EXCAVATION, $xpreward);
		}
	}
}