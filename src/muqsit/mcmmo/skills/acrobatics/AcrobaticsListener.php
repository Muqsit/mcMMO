<?php
namespace muqsit\mcmmo\skills\acrobatics;

use muqsit\mcmmo\skills\SkillListener;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AcrobaticsListener extends SkillListener{

	/** @var AcrobaticsConfig */
	private $config;

	protected function init() : void{
		$this->config = new AcrobaticsConfig();
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority HIGH
	 * @ignoreCancelled true
	 */
	public function onEntityDamage(EntityDamageEvent $event) : void{
		$player = $event->getEntity();
		if($player instanceof Player){
			$manager = $this->plugin->getSkillManager($player);
			$skill = $manager->getSkill(self::ACROBATICS);

			if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
				$graceful = $player->isSneaking();
				$damage = $event->getFinalDamage();
				if($damage <= $this->config->getRollDamageThreshold($graceful)){
					$chance = ($graceful ? $skill->getGracefulRollChance() : $skill->getRollChance()) * 100;
					if(mt_rand($chance, 10000) <= $chance){
						$event->setCancelled();
						$manager->addSkillXp(self::ACROBATICS, $this->config->getXpReward($graceful ? AcrobaticsConfig::TYPE_GRACEFUL_ROLL : AcrobaticsConfig::TYPE_ROLL, $damage));
						$player->sendMessage($graceful ? TextFormat::GREEN . "**Graceful Landing**" : TextFormat::WHITE . "**Rolled**");
					}
				}
				return;
			}

			if($event instanceof EntityDamageByEntityEvent){
				$chance = $skill->getDodgeChance() * 100;
				if(mt_rand($chance, 10000) <= $chance){
					$event->setDamage($event->getDamage() / $this->config->getDodgeDamageModifier());
					$manager->addSkillXp(self::ACROBATICS, $this->config->getXpReward(AcrobaticsConfig::TYPE_DODGE, $damage));
					$player->sendMessage(TextFormat::GREEN . "**Dodged**");
				}
			}
		}
	}
}