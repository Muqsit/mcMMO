<?php
namespace muqsit\mcmmo\skills\woodcutting;

use muqsit\mcmmo\skills\SkillListener;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\sound\PopSound;

class WoodcuttingListener extends SkillListener{

	/** @var WoodcuttingConfig */
	private $config;

	protected function init() : void{
		$this->config = new WoodcuttingConfig();
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @priority HIGH
	 * @ignoreCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();
		$item = $event->getItem();

		if($this->config->isLeaf($block) && $this->config->isRightTool($item)){
			$player = $event->getPlayer();
			$level = $this->plugin->getSkillManager($player)->getSkill(self::WOODCUTTING)->getLevel();
			if($level >= WoodcuttingConfig::MINIMUM_LEAFBLOWER_LEVEL){
				$level = $block->getLevel();
				$level->useBreakOn($block, $item, $player);
				$level->addSound(new PopSound($block));
			}
		}
	}

	/**
	 * @param BlockBreakEvent
	 * @priority HIGH
	 * @ignoreCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		$player = $event->getPlayer();
		$manager = $this->plugin->getSkillManager($player);
		$skill = $manager->getSkill(self::WOODCUTTING);
		$event->setDrops($this->config->getDrops($player, $event->getItem(), $event->getBlock(), $skill->getLevel(), $skill->hasAbility(), $xpreward));

		if($xpreward > 0){
			$manager->addSkillXp(self::WOODCUTTING, $xpreward);
		}
	}
}